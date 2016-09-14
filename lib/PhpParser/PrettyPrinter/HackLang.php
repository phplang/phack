<?php

namespace PhpLang\Phack\PhpParser\PrettyPrinter;

use PhpLang\Phack\PhpParser\Node\Expr;
use PhpLang\Phack\PhpParser\Node\Stmt;

use \PhpParser\Node as pNode;
use \PhpParser\Node\Expr as pExpr;
use \PhpParser\Node\Scalar as pScalar;
use \PhpParser\Node\Stmt as pStmt;

/**
 * Things we do to HackLang syntax:
 *
 * 1) Complete type erasure, even for types that PHP could verify
 *   - HH Type checker should be doing these checks for us, trust it.
 *   - No runtime cost, because nothing to check
 *   - Easier to implement if we're not dealing with Generic mappings
 * 2) Transform short lambda `==>` to a standard closure
 *   - Track variables in layered scopes to auto-populate `use` clause
 * 3) Transform `Enum` definitions into traditional classes
 *   - usePhpLang\Phack\Lib\EnumMethods
 *   - Enum values as consts
 *   - Set privaye props mirroring enums for quick reflection
 */
class HackLang extends \PhpParser\PrettyPrinter\Standard {
    /* @var array Current tracked variable scope for lambdas */
    protected $lambdaScope = array(array());

    public function __construct(array $options = array()) {
        $this->precedenceMap['Expr_Lambda'] = array(65, 1);
        parent::__construct($options);
    }

    protected function pushScopeVar($name) {
        if (!($name instanceof pExpr)) {
            $this->lambdaScope[count($this->lambdaScope) - 1][$name] = true;
        }
    }

    public function pParam(pNode\Param $param) {
        $t = $param->type;
        $param->type = null;
        $ret = parent::pParam($param);
        $param->type = $t;
        return $ret;
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
        $impl = $this->p($lambda->closure);
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
        return $ret . " { return $impl; }";
    }

    public function pStmt_Function(pStmt\Function_ $func) {
        array_push($this->lambdaScope, array());
        $rt = $func->returnType;
        $func->returnType = null;
        $ret = parent::pStmt_Function($func);
        $func->returnType = $rt;
        array_pop($this->lambdaScope);
        return $ret;
    }

    public function pStmt_Class(pStmt\Class_ $cls) {
        // Classes don't really have a scope, but props get picked up greedily
        // So stash them in this psuedo scope where they won't hurt anyone
        array_push($this->lambdaScope, array());
        $ret = parent::pStmt_Class($cls);
        array_pop($this->lambdaScope);
        return $ret;
    }

    public function pStmt_Enum(Stmt\Enum $enum) {
        // Triplicate the const values
        // First as const statements for Foo::BAR access
        // Second in a private prop for getValues()
        // Third as another private prop for assert/assertAll/coerce/isValid/getNames
        $consts = array();
        foreach ($enum->stmts as $stmt) {
            if ($stmt instanceof pStmt\Const_) {
                $consts += $stmt->consts;
            }
        }
        $orig_stmts = $enum->stmts;
        if ($consts) {
            $names = $values = array();
            foreach ($consts as $const) {
                $name = new pScalar\String_($const->name, array('kind'=> pScalar\String_::KIND_SINGLE_QUOTED));
                $names[]  = new pExpr\ArrayItem($name, $const->value);
                $values[] = new pExpr\ArrayItem($const->value, $name);
            }
            $enum->stmts[] = new pStmt\Property(pStmt\Class_::MODIFIER_PRIVATE | pStmt\Class_::MODIFIER_STATIC, array(
                new pStmt\PropertyProperty('names', new pExpr\Array_($names)),
                new pStmt\PropertyProperty('values', new pExpr\Array_($values)),
            ));
            $enum->stmts[] = new pStmt\Use_(array(
                new pStmt\UseUse(new pNode\Name('\PhpLang\Phack\Lib\EnumMethods')),
            ));
        }
        $ret = $this->pStmt_Class($enum);
        $enum->stmts = $orig_stmts;
        return $ret;
    }

    public function pStmt_ClassMethod(pStmt\ClassMethod $func) {
        array_push($this->lambdaScope, array());
        $rt = $func->returnType;
        $func->returnType = null;
        $ret = parent::pStmt_ClassMethod($func);
        $func->returnType = $rt;
        array_pop($this->lambdaScope);
        return $ret;
    }

}
