<?php

namespace PhpLang\Phack\PhpParser\Node\Expr;

class Lambda extends \PhpParser\Node\Expr {
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var array Parameters */
    public $params;
    /** @var Stmt[] Closure statements */
    public $stmts;

    /**
     * Constructs a Lambda node.
     *
     * @param array  $params     Lambda parameters
     * @param Stmt[] $stmts      Expression
     * @param array  $attributes Additional attributes
     */
    public function __construct(array $params, array $stmts, array $attributes = array()) {
        parent::__construct($attributes);
        $this->params = $params;
        $this->stmts = $stmts;
    }

    public function getSubNodeNames() {
        return array('params', 'stmts');
    }
}
