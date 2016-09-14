<?php

namespace PhpLang\Phack\PhpParser\Node\Expr;

class Lambda extends \PhpParser\Node\Expr {
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var array Parameters */
    public $params;
    /** @var Expr Closure */
    public $closure;

    /**
     * Constructs a Lambda node.
     *
     * @param array $params     Lambda parameters
     * @param Expr  $expr       Expression
     * @param array $attributes Additional attributes
     */
    public function __construct(array $params, \PhpParser\Node\Expr $closure, array $attributes = array()) {
        parent::__construct($attributes);
        $this->params = $params;
        $this->closure = $closure;
    }

    public function getSubNodeNames() {
        return array('params', 'closure');
    }
}
