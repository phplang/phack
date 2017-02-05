<?php

namespace PhpLang\Phack\PhpParser\Node\Stmt;

class ClassMethod extends \PhpParser\Node\Stmt\ClassMethod
{
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var typename[] Generics typename */
    public $generics;

    /** @var UserAttribute[] HackLang user attributes */
    public $user_attributes;

    /**
     * Constructs a class method node.
     *
     * @param string      $name       Name
     * @param array       $subNodes   Array of the following optional subnodes:
     *                                'type'       => MODIFIER_PUBLIC: Type
     *                                'byRef'      => false          : Whether to return by reference
     *                                'params'     => array()        : Parameters
     *                                'returnType' => null           : Return type
     *                                'stmts'      => array()        : Statements
     *                                'generics'   => array()        : Typenames
     *                                'user_attributes' => UserAttribites[]: User Attributes
     * @param array       $attributes Additional attributes
     */
    public function __construct($name, array $subNodes = [], array $attributes = []) {
        parent::__construct($name, $subNodes, $attributes);
        $this->generics = isset($subNodes['generics']) ? $subNodes['generics'] : array();
        $this->user_attributes = isset($subNodes['user_attributes'])
                                     ? $subNodes['user_attributes'] : array();
        if (strcasecmp($this->name, '__construct')) {
            foreach ($this->params as $param) {
                if (!($param instanceof \PhpLang\Phack\PhpParser\Node\Param)) continue;
                if ($param->visibility === null) return;
                throw new Error('Only constructor args may have visibility', $attributes);
            }
        }
    }

    public function getSubNodeNames() {
        return array_merge(parent::getSubNodeNames(), array('generics', 'user_attributes'));
    }
}
