<?php

namespace PhpLang\Phack\PhpParser\Node\Stmt;

class Class_ extends \PhpParser\Node\Stmt\Class_
{
	use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var typename[] */
    public $generics;

    /** @var UserAttribute[] */
    public $user_attributes;

    /**
     * Constructs a class node.
     *
     * @param string|null $name       Name
     * @param array       $subNodes   Array of the following optional subnodes:
     *                                'type'       => 0      : Type
     *                                'extends'    => null   : Name of extended class
     *                                'implements' => array(): Names of implemented interfaces
     *                                'stmts'      => array(): Statements
     *                                'generics'   => array(): Typename
     *                                'user_attributes' => UserAttribites[]: User Attributes
     * @param array       $attributes Additional attributes
     */
    public function __construct($name, array $subNodes = array(), array $attributes = array()) {
        parent::__construct($name, $subNodes, $attributes);
        $this->generics = isset($subNodes['generics'])
                              ? $subNodes['generics'] : array();
        $this->user_attributes = isset($subNodes['user_attributes'])
                                     ? $subNodes['user_attributes'] : array();
    }

    public function getSubNodeNames() {
        return array_merge(parent::getSubNodeNames(), array('generics', 'user_attributes'));
    }
}
