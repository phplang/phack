<?php

namespace PhpLang\Phack\PhpParser\PrettyPrinter;

use PhpLang\Phack\PhpParser\Node;
use PhpLang\Phack\PhpParser\Node\Expr;
use PhpLang\Phack\PhpParser\Node\Stmt;

use \PhpParser\Node as pNode;
use \PhpParser\Node\Expr as pExpr;
use \PhpParser\Node\Scalar as pScalar;
use \PhpParser\Node\Stmt as pStmt;

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

    public function __construct(array $options = array()) {
        $this->precedenceMap['Expr_Lambda'] = array(65, 1);
        parent::__construct($options);
    }

    protected function pushScopeVar($name) {
        if (!($name instanceof pExpr)) {
            $this->lambdaScope[count($this->lambdaScope) - 1][$name] = true;
        }
    }

    protected static function resolveTypename($type) {
        if ($type === null) return '';
        if (is_string($type)) return $type;
        assert(is_object($type), 'Expecting placeholder typename, got intrinsic: '.print_r($type, true));
        if ($type instanceof pNode\Name) {
            return implode('\\', $type->parts);
        } elseif ($type instanceof Node\GenericsType) {
            return self::resolveTypename($type->basetype);
        } elseif ($type instanceof Node\GenericsTypeAs) {
            return self::resolveTypename($type->type);
        } elseif ($type instanceof Node\CallableType) {
            return 'callable';
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

    public function pGenericsType(Node\GenericsType $type) {
        if ($this->isPlaceholder($type)) return '';
        return is_object($type->basetype) ? $this->p($type->basetype)
                                          : ((string)$type->basetype);
    }

    public function pParam(pNode\Param $param) {
        $type = $param->type;
        if ($this->isPlaceholder($type)) {
            $param->type = null;
        }
        $ret = parent::pParam($param);
        $param->type = $type;
        return $ret;
    }

    public function pCallableType(Node\CallableType $callable) {
        return 'callable';
    }

    public function pExpr_Variable(pExpr\Variable $var) {
        $this->pushScopeVar($var->name);
        return parent::pExpr_Variable($var);
    }

    public function pExpr_Closure(pExpr\Closure $closure) {
        array_push($this->lambdaScope, array());
        $ret = parent::pExpr_Closure($closure);
        array_pop($this->lambdaScope);
        return $ret;
    }

    public function pExpr_ClosureUse(pExpr\ClosureUse $use) {
        $this->pushScopeVar($use->var);
        return parent::pExpr_ClosureUse($use);
    }

    public function pExpr_Lambda(Expr\Lambda $lambda) {
        $parentScope = $this->lambdaScope[count($this->lambdaScope) - 1];

        array_push($this->lambdaScope, array());
        if ((count($lambda->stmts) === 1)
             && ($lambda->stmts[0] instanceof pStmt\Return_)) {
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

    public function pStmt_Function(pStmt\Function_ $func) {
        if ($func instanceof Stmt\Function_) {
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

        if ($func instanceof Stmt\Function_) {
            $this->popGenerics($func->generics);
        }
        return $ret;
    }

    public function pStmt_Class(pStmt\Class_ $cls) {
        if ($cls instanceof Stmt\Class_) {
            $this->pushGenerics($cls->generics);
        }

        // Classes don't really have a scope, but props get picked up greedily
        // So stash them in this psuedo scope where they won't hurt anyone
        array_push($this->lambdaScope, array());
        $ret = parent::pStmt_Class($cls);
        array_pop($this->lambdaScope);

        if ($cls instanceof Stmt\Class_) {
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
    public function pStmt_Enum(Stmt\Enum $enum) {
        // Triplicate the const values
        // First as const statements for Foo::BAR access
        // Second in a private prop for getValues()
        // Third as another private prop for assert/assertAll/coerce/isValid/getNames

        $names = $values = array();
        foreach ($enum->values as $const) {
            $name = new pScalar\String_($const->name, array('kind'=> pScalar\String_::KIND_SINGLE_QUOTED));
            $names[]  = new pExpr\ArrayItem($name, $const->value);
            $values[] = new pExpr\ArrayItem($const->value, $name);
        }

        $stmts = array(
            new pStmt\Use_(array(
                new pStmt\UseUse(new pNode\Name('\PhpLang\Phack\Lib\EnumMethods')),
            )),
            new pStmt\Property(pStmt\Class_::MODIFIER_PRIVATE |
                               pStmt\Class_::MODIFIER_STATIC, array(
                new pStmt\PropertyProperty('names', new pExpr\Array_($names)),
                new pStmt\PropertyProperty('values', new pExpr\Array_($values)),
            )),
        );

        if ($enum->values) {
            $stmts[] = new pStmt\Const_($enum->values);
        }

        $cls = new pStmt\Class_(
            $enum->name,
            array(
                'type'  => pStmt\Class_::MODIFIER_ABSTRACT,
                'stmts' => $stmts,
            )
        );

        return $this->pStmt_Class($cls);
    }

    public function pStmt_ClassMethod(pStmt\ClassMethod $func) {
        if ($func instanceof Stmt\ClassMethod) {
            $this->pushGenerics($func->generics);
        }

        array_push($this->lambdaScope, array());
        $rt = $func->returnType;
        $func->returnType = null;
        $ret = parent::pStmt_ClassMethod($func);
        $func->returnType = $rt;
        array_pop($this->lambdaScope);

        if ($func instanceof Stmt\ClassMethod) {
            $this->popGenerics($func->generics);
        }
        return $ret;
    }

}
