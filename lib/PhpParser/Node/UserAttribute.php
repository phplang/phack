<?php

namespace PhpLang\Phack\PhpParser\Node;

use \PhpParser\Node as pNode;

class UserAttribute extends \PhpParser\NodeAbstract
{
    use GetType;

    /** @var pNode\Name Name of the attribute */
    public $name;

    /** @var array Attribute values */
    public $values;

    /**
     * Constructs a name node.
     *
     * @param pNode\Name $name     Name of the user user attribute
     * @param array      $values   Attribute values
     * @param array      $attributes Additional attributes
     */
    public function __construct(pNode\Name $name, array $values = array(), array $attributes = [])
    {
        parent::__construct($attributes);
        $this->name = $name;
        $this->values = $values;
    }

    public function getSubNodeNames() {
        return array('name','values');
    }
}
