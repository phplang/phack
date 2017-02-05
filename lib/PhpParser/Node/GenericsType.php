<?php

namespace PhpLang\Phack\PhpParser\Node;

use \PhpParser\Node as pNode;

class GenericsType extends \PhpParser\NodeAbstract
{
    use GetType;

    /** @var string|Name BaseType */
    public $basetype;

    /** @var string[]|Name[]|GenericsTypeAlias[] Subtypes */
    public $subtypes;

    /**
     * Constructs a name node.
     *
     * @param string|Name                       $basetype   Base name of Generics Type
     * @param (string|Name|GenericsTypeAlias)[] $subtypes   Generics Subtypes
     * @param array                             $attributes Additional attributes
     */
    public function __construct($basetype, array $subtypes = array(), array $attributes = array()) {
        parent::__construct($attributes);
        $this->basetype = $basetype;
        $this->subtypes = $subtypes;
    }

    public function getSubNodeNames() {
        return array('basename','subtypes');
    }
}
