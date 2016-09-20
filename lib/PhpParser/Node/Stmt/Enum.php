<?php

namespace PhpLang\Phack\PhpParser\Node\Stmt;
use \PhpParser\Node\Name as pName;
use \PhpParser\Node\Stmt as pStmt;

class Enum extends pStmt {
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var string Enum Name */
    public $name;

    /** @var string Underlying storage type */
    public $type;

    /** @var Expr\Const_[] Declared Enum values */
    public $values;

    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';

    /**
     * Constructs an Enum node
     *
     * @param string $name       Class name
     * @param string $type       Base type (Usually 'int' or 'string')
     * @param array  $values     Const value pairings
     * @param array  $attributes Additional attributes
     */
    public function __construct($name, $type, array $values, array $attributes = array()) {
        $this->name = $name;
        $this->type = $type;
        $this->values = $values;
        parent::__construct($attributes);
    }

    public function getSubNodeNames() {
        return array('name', 'type', 'values');
    }
}
