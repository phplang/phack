<?php

namespace PhpLang\Phack\PhpParser\Node;

class GenericsConstraint extends \PhpParser\NodeAbstract
{
    use GetType;

    const AS_TYPE = 1;
    const SUPER = 2;

    /** @var string|pNode\Name Typename Placeholder */
    public $name;

    /** @var int Constraint: self::AS_TYPE or self::SUPER */
    public $rel;

    /** @var string|pNode\Name Type As/Super (co/contra variance) */
    public $constraint;

    /**
     * Constructs a name node.
     *
     * @param string|pNode\Name $name       Typename Placeholder
     * @param int               $rel        self::AS_TYPE or self::SUPER
     * @param string|pNode\Name $constraint Type As/Super (co/contra variance)
     * @param array             $attributes Additional attributes
     */
    public function __construct($name, $rel, $constraint,  array $attributes = array()) {
        parent::__construct($attributes);
        $this->name = $name;
        $this->rel = $rel;
        $this->constraint = $constraint;
    }

    public function getSubNodeNames() {
        return array('name','rel','constraint');
    }
}
