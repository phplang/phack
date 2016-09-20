<?php

namespace PhpLang\Phack\PhpParser\Node;

use \PhpParser\Node as pNode;

class GenericsTypeAs extends \PhpParser\NodeAbstract
{
    use GetType;

    /** @var string | pNode\Name, Typename Placeholder */
    public $name;

    /** @var string | pNode\Name Type As (covariance) */
    public $as;

    /**
     * Constructs a name node.
     *
     * @param string | pNode\Name $type       Typename Placeholder
     * @param string | pNode\Name $as         Type As (covariance)
     * @param array               $attributes Additional attributes
     */
    public function __construct($name, $as = null,  array $attributes = array()) {
        parent::__construct($attributes);
        $this->name = $name;
        $this->as = $as;
    }

    public function getSubNodeNames() {
        return array('name','as');
    }
}
