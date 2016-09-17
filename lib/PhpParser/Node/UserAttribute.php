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
     * @param string|array $parts      Parts of the name (or name as string)
     * @param array        $attributes Additional attributes
     */
    public function __construct(pNode\Name $name, array $values = array(),  array $attributes = array()) {
        parent::__construct($attributes);
        $this->name = $name;
        $this->values = $values;
    }

    public function getSubNodeNames() {
        return array('name','values');
    }
}
