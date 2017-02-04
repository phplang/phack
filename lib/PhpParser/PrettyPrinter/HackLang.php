<?php

namespace PhpLang\Phack\PhpParser\PrettyPrinter;

use PhpLang\Phack\PhpParser\Node as PhackNode;
use PhpParser\Node as ParserNode;

/**
 * Things we do to HackLang syntax:
 *
 * 1) Partial type erasure, down to concrete base types
 *   - HH Type checker should be doing these checks for us, trust it.
 * 2) Transform short lambda `==>` to a standard closure
 *   - Track variables in layered scopes to auto-populate `use` clause
 * 3) Transform `Enum` definitions into traditional classes
 *   - use PhpLang\Phack\Lib\EnumMethods
 *   - Enum values as consts
 *   - Set private props mirroring enums for quick reflection
 */
class HackLang extends \PhpParser\PrettyPrinter\Standard {
    /** @var string[] Current tracked variable scope for lambdas */
    protected $lambdaScope = array(array());

    /** @var int[string] Generics placeholder types */
    protected $genericsTypes = array();

    /** @var Expr[] LHS of current/nested pipe expressions */
    protected $pipes = array();

    public function __construct(array $options = array()) {
        $this->precedenceMap['Expr_Lambda'] = array(65, 1);
        parent::__construct($options);
    }

    protected function pushScopeVar($name) {
        if (!($name instanceof ParserNode\Expr)) {
            $this->lambdaScope[count($this->lambdaScope) - 1][$name] = true;
        }
    }

    protected static function resolveTypename($type) {
        if ($type === null) return '';
        if (is_string($type)) return $type;
        assert(is_object($type), 'Expecting placeholder typename, got intrinsic: '.print_r($type, true));
        if ($type instanceof ParserNode\Name) {
            return $type->toString();
        } elseif ($type instanceof PhackNode\GenericsType) {
            return self::resolveTypename($type->basetype);
        } elseif ($type instanceof PhackNode\GenericsConstraint) {
            return self::resolveTypename($type->name);
        } elseif ($type instanceof PhackNode\CallableType) {
            return 'callable';
        } elseif ($type instanceof PhackNode\SoftNullableType) {
            /* TODO: Log type misses */
            /* TODO: Deal with nullable checking for non-optionals */
            return '';
        } else {
            assert(false, "Unknown placeholder typename".print_r($type, true));
            return false;
        }
    }

    protected function pushGenerics(array $generics) {
        foreach ($generics as $g) {
            $type = self::resolveTypename($g);
            if (!empty($this->generics[$type])) {
                ++$this->generics[$type];
            } else {
                $this->generics[$type] = 1;
            }
        }
    }

    protected function popGenerics(array $generics) {
        foreach ($generics as $g) {
            $type = self::resolveTypename($g);
            if (!empty($this->generics[$type])) {
                --$this->generics[$type];
            } else {
                $this->generics[$type] = 0;
            }
        }
    }

    protected function isPlaceholder($type) {
        if (($type === null) || ($type === array())) return false;
        $type = self::resolveTypename($type);
        return !empty($this->generics[$type]);
    }

    public function pGenericsType(PhackNode\GenericsType $type) {
        if ($this->isPlaceholder($type)) return '';
        return is_object($type->basetype) ? $this->p($type->basetype)
                                          : ((string)$type->basetype);
    }

    public function pSoftNullableType(PhackNode\SoftNullableType $type) {
        return self::resolveTypename($type);
    }

    public function pParam(ParserNode\Param $param) {
        $type = $param->type;
        if ($this->isPlaceholder($type)) {
            $param->type = null;
        }
        $ret = parent::pParam($param);
        $param->type = $type;

        return $ret;
    }

    public function pCallableType(PhackNode\CallableType $callable) {
        return 'callable';
    }

    public function pExpr_Variable(ParserNode\Expr\Variable $var) {
        $this->pushScopeVar($var->name);
        return parent::pExpr_Variable($var);
    }

    public function pExpr_Closure(ParserNode\Expr\Closure $closure) {
        array_push($this->lambdaScope, array());
        $ret = parent::pExpr_Closure($closure);
        array_pop($this->lambdaScope);
        return $ret;
    }

    public function pExpr_ClosureUse(ParserNode\Expr\ClosureUse $use) {
        $this->pushScopeVar($use->var);
        return parent::pExpr_ClosureUse($use);
    }

    public function pExpr_Lambda(PhackNode\Expr\Lambda $lambda) {
        $parentScope = $this->lambdaScope[count($this->lambdaScope) - 1];

        array_push($this->lambdaScope, array());
        if ((count($lambda->stmts) === 1)
             && ($lambda->stmts[0] instanceof ParserNode\Stmt\Return_)) {
            $impl = ' { ' . $this->p($lambda->stmts[0]) . ' }';
        } else {
            $impl = ' {' . $this->pStmts($lambda->stmts) . "\n}";
        }
        $childScope = array_pop($this->lambdaScope);

        $use = array();
        foreach ($childScope as $varname => $dummy) {
            if (isset($parentScope[$varname])) {
                $use[] = $varname;
            }
        }

        $ret = 'function (' . $this->pCommaSeparated($lambda->params) . ')';
        if (!empty($use)) {
            $ret .= ' use ($' . implode(', $', $use) . ')';
        }
        return $ret . $impl;
    }

    public function pExpr_Pipe(PhackNode\Expr\Pipe $pipe) {
        array_push($this->pipes, $pipe->lhs);
        $ret = $this->p($pipe->rhs);
        if (null !== array_pop($this->pipes)) {
            throw new \Exception('LHS of pipe expression was not used');
        }
        return $ret;
    }

    public function pExpr_PipeVar(PhackNode\Expr\PipeVar $var) {
        if (count($this->pipes) === 0) {
            throw new \Exception('$$ used outside of a pipe expression');
        }
        $expr = array_pop($this->pipes);
        if (null === $expr) {
            throw new \Exception('Only one instance of $$ is allowed in a pipe expression');
        }
        $ret = $this->p($expr);
        array_push($this->pipes, null);
        return $ret;
    }

    public function pStmt_Function(ParserNode\Stmt\Function_ $func) {
        if ($func instanceof PhackNode\Stmt\Function_) {
            $this->pushGenerics($func->generics);
        }

        array_push($this->lambdaScope, array());
        $rt = $func->returnType;
        if ($this->isPlaceholder($func->returnType)) {
            $func->returnType = null;
        }
        $ret = parent::pStmt_Function($func);
        $func->returnType = $rt;
        array_pop($this->lambdaScope);

        if ($func instanceof PhackNode\Stmt\Function_) {
            $this->popGenerics($func->generics);
        }
        return $ret;
    }

    public function pStmt_Class(ParserNode\Stmt\Class_ $cls) {
        if ($cls instanceof PhackNode\Stmt\Class_) {
            $this->pushGenerics($cls->generics);
        }

        // Classes don't really have a scope, but props get picked up greedily
        // So stash them in this psuedo scope where they won't hurt anyone
        array_push($this->lambdaScope, array());

        $stmts = $cls->stmts;
        $ctor = null;
        $commented_properties = [];

        foreach ($cls->stmts as $idx => &$stmt) {
            if ($stmt instanceof ParserNode\Stmt\Property) {
                foreach ($stmt->props as $prop) {
                    $new_property = new ParserNode\Stmt\Property($stmt->type, array(
                        new ParserNode\Stmt\PropertyProperty($prop->name, $prop->default),
                    ));
                    if ($prop->type) {
                        $new_property->setAttribute('comments', array(
                            new \PhpParser\Comment\Doc('/** @var ' . $prop->type . ' */'),
                        ));
                    }
                    $commented_properties[] = $new_property;
                }

                $stmt = null;
            }

            if (!($stmt instanceof PhackNode\Stmt\ClassMethod)) continue;
            if (strcasecmp($stmt->name, '__construct')) continue;
            $ctor = $stmt;
            $ctor_stmts = $ctor->stmts;
            foreach ($stmt->params as $param) {
                if (!($param instanceof PhackNode\Param)) {
                    continue;
                }
                if ($param->visibility === null) continue;

                $cls->stmts[] = new ParserNode\Stmt\Property($param->visibility, array(
                    new ParserNode\Stmt\PropertyProperty($param->name),
                ));
                array_unshift($ctor->stmts, new ParserNode\Expr\Assign(
                    new ParserNode\Expr\PropertyFetch(
                        new ParserNode\Expr\Variable('this'),
                        $param->name
                    ),
                    new ParserNode\Expr\Variable($param->name)
                ));
            }
        }

        $cls->stmts = $commented_properties + array_filter($cls->stmts);

        var_dump($cls->stmts);

        //$cls->stmts = array_filter($cls->stmts);

        $ret = parent::pStmt_Class($cls);

        // Restore state
        $cls->stmts = $stmts;
        if ($ctor !== null) {
            $ctor->stmts = $ctor_stmts;
        }
        array_pop($this->lambdaScope);
        if ($cls instanceof PhackNode\Stmt\Class_) {
            $this->popGenerics($cls->generics);
        }
        return $ret;
    }

    /**
     * Repurpose pStmt_Class to render the Enum for PHP
     * Marshal the values into three replicated structures.
     * 1) Actual const values for Enum::ELEMENT
     * 2) private $names array for value to name reverse mapping/reflection
     * 3) private $values array for forward mapping/reflection
     */
    public function pStmt_Enum(PhackNode\Stmt\Enum $enum) {
        // Triplicate the const values
        // First as const statements for Foo::BAR access
        // Second in a private prop for getValues()
        // Third as another private prop for assert/assertAll/coerce/isValid/getNames

        $names = $values = array();
        foreach ($enum->values as $const) {
            $name = new ParserNode\Scalar\String_($const->name,
                array('kind'=> ParserNode\Scalar\String_::KIND_SINGLE_QUOTED));
            $names[]  = new ParserNode\Expr\ArrayItem($name, $const->value);
            $values[] = new ParserNode\Expr\ArrayItem($const->value, $name);
        }

        $stmts = array(
            new ParserNode\Stmt\Use_(array(
                new ParserNode\Stmt\UseUse(new ParserNode\Name('\PhpLang\Phack\Lib\EnumMethods')),
            )),
            new ParserNode\Stmt\Property(ParserNode\Stmt\Class_::MODIFIER_PRIVATE |
                                        ParserNode\Stmt\Class_::MODIFIER_STATIC, array(
                new ParserNode\Stmt\PropertyProperty('names', new ParserNode\Expr\Array_($names)),
                new ParserNode\Stmt\PropertyProperty('values', new ParserNode\Expr\Array_($values)),
            )),
        );

        if ($enum->values) {
            $stmts[] = new ParserNode\Stmt\Const_($enum->values);
        }

        $cls = new ParserNode\Stmt\Class_(
            $enum->name,
            array(
                'type'  => ParserNode\Stmt\Class_::MODIFIER_ABSTRACT,
                'stmts' => $stmts,
            )
        );

        return $this->pStmt_Class($cls);
    }

    public function pStmt_ClassMethod(ParserNode\Stmt\ClassMethod $func) {
        if ($func instanceof PhackNode\Stmt\ClassMethod) {
            $this->pushGenerics($func->generics);
        }

        array_push($this->lambdaScope, array());
        $rt = $func->returnType;
        $func->returnType = null;
        $ret = parent::pStmt_ClassMethod($func);
        $func->returnType = $rt;
        array_pop($this->lambdaScope);

        if ($func instanceof PhackNode\Stmt\ClassMethod) {
            $this->popGenerics($func->generics);
        }
        return $ret;
    }

}
