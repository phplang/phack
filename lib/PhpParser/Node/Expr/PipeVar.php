<?php

namespace PhpLang\Phack\PhpParser\Node\Expr;

class PipeVar extends \PhpParser\Node\Expr {
    use \PhpLang\Phack\PhpParser\Node\GetType;

    public function getSubNodeNames() {
        return array();
    }
}
