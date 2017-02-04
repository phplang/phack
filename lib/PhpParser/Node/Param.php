<?php

namespace PhpLang\Phack\PhpParser\Node;
use \PhpParser\Node\Expr as pExpr;

class Param extends \PhpParser\Node\Param {
    use GetType;

    /** @var int Visibility (for constructor arg promotion) */
    public $visibility;

    /**
     * Constructs a parameter node.
     *
     * @param string           $name       Name
     * @param null|pExpr       $default    Default value
     * @param null|string|Name $type       Typehint
     * @param bool             $byRef      Whether is passed by reference
     * @param bool             $variadic   Whether this is a variadic argument
     * @param int              $visibility For constructor arg promotion
     * @param array            $attributes Additional attributes
     */
    public function __construct(
        $name,
        pExpr $default = null,
        $type = null,
        $byRef = false,
        $variadic = false,
        $visibility = null,
        array $attributes = []
    ) {
        parent::__construct($name, $default, $type, $byRef, $variadic, $attributes);
        $this->visibility = $visibility;
    }

    public function getSubNodeNames() {
        return array_merge(parent::getSubNodeNames(), array('visibility'));
    }
}
