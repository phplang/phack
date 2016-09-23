<?php

namespace PhpLang\Phack\PhpParser\Node;

use \PhpParser\Node as pNode;

class SoftNullableType extends \PhpParser\NodeAbstract
{
    use GetType;

    /** @var bool Soft typehint */
    public $soft;

    /** @var bool Nullable */
    public $nullable;

    /** @var type Underlying type */
    public $type;

    /**
     * Constructs a name node.
     *
     * @param type         $type       Underlying type
     * @param bool         $soft       Soft typehint
     * @param bool         $nullable   Nullable typehint
     * @param array        $attributes Additional attributes
     */
    public function __construct($type, $soft, $nullable,
                                array $attributes = array()) {
        parent::__construct($attributes);
        $this->type = $type;
        $this->soft = $soft;
        $this->nullable = $nullable;
        if ($type instanceof self) {
            $this->type = $type->type;
            $this->soft |= $type->soft;
            $this->nullable |= $type->nullable;
        }
    }

    public function getSubNodeNames() {
        return array('type', 'soft', 'nullable');
    }
}
