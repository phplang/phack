<?php

namespace PhpLang\Phack\PhpParser\Node\Expr;
use PhpParser\Node as pNode;

class Pipe extends pNode\Expr {
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var Expr DataSource expression */
    public $lhs;
    /** @var Expr Consumer expression */
    public $rhs;

    /**
     * Constructs a Pipe node.
     *
     * @param Expr  $lhs        Datasource expression
     * @param Expr  $rhs        Consumer expression
     * @param array $attributes Additional attributes
     */
    public function __construct(pNode\Expr $lhs, pNode\Expr $rhs, array $attributes = array()) {
        parent::__construct($attributes);
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    public function getSubNodeNames() {
        return array('lhs', 'rhs');
    }
}
