<?php

use PhpLang\Phack\Test;

class PhackTrailingCommaTest extends PHPUnit\Framework\TestCase {
    use Test\AssertTranspilesTrait;

    public function testFunctionDecl() {
        $this->assertTranspiles('function foo($bar, $baz) { }', 'function foo($bar, $baz,) {}');
    }

    public function testFunctionCall() {
        $this->assertTranspiles('foo($bar, $baz);', 'foo($bar, $baz,);');
    }
}
