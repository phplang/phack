<?php

namespace PhpLang\Phack\PhpParser\Node\Stmt;

use \PhpParser\Node as ParserNode;

class PropertyProperty extends ParserNode\Stmt\PropertyProperty
{
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var ?Type Property type */
    public $type;

    /**
     * Constructs a class property node.
     *
     * @param string                $name       Name
     * @param null|ParserNode\Expr  $default    Default value
     * @param ?Type                 $type       Property type
     * @param array                 $attributes Additional attributes
     */
    public function __construct($name, ParserNode\Expr $default = null,
                                $type, array $attributes = array()) {
        parent::__construct($name, $default, $attributes);
        $this->type = $type;
    }

    public function getSubNodeNames() {
        return array_merge(parent::getSubNodeNames(), array('type'));
    }
}
