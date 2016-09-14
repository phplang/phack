<?php

namespace PhpLang\Phack\PhpParser\Node\Stmt;
use \PhpParser\Node\Name as pName;
use \PhpParser\Node\Stmt as pStmt;

class Enum extends pStmt\Class_ {
    use \PhpLang\Phack\PhpParser\Node\GetType;

    /** @var string Underlying storage type */
    public $type;

    /**
     * Constructs an Enum node
     *
     * @param string $name       Class name
     * @param string $type       Base type (Usually 'int' or 'string')
     * @param array  $values     Const value pairings
     * @param array  $attributes Additional attributes
     */
    public function __construct($name, $type, array $values, array $attributes = array()) {
        $this->type = $type;
        return parent::__construct($name, array(
            'type'    => pStmt\Class_::MODIFIER_ABSTRACT,
            'stmts'   => $values ? array(new pStmt\Const_($values)) : array(),
        ), $attributes);
    }

    public function getSubNodeNames() {
        return parent::getSubNodeNames() + array('type');
    }
}
