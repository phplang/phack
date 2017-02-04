<?php

namespace PhpLang\Phack\PhpParser\Node;

use \PhpParser\Node as pNode;

class CallableType extends \PhpParser\NodeAbstract
{
    use GetType;

    /** @var (SoftNullableType|GenericsType|CallableType)[] Callable parameters */
    public $parameters;

    /** @var bool Variadic */
    public $variadic;

    /** @var SoftNullableType|GenericsType|CallableType|null Retur type */
    public $returnType;

    /** @var bool Returns Reference */
    public $returnRef;

    /**
     * Constructs a name node.
     *
     * @param (SoftNullableType|GenericsType|CallableType)[]       $parameters   Parameter Types
     * @param SoftNullableType|GenericsType|CallableType|null        $returnType   Return Type
     * @param bool         $returnRef    Returns Reference
     * @param array        $attributes   Additional attributes
     */
    public function __construct(
        array $parameters,
        $returnType,
        $returnRef,
        array $attributes = []
    ) {
        parent::__construct($attributes);
        /* Parameters overloaded with last element being true/false variadic flag */
        $this->variadic = array_pop($parameters);
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->returnRef = $returnRef;
    }

    public function getSubNodeNames() {
        return array('parameters', 'variadic', 'returnType', 'returnRef');
    }
}
