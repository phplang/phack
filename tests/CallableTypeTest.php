<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack\Test;

class PhackCallableTypeTest extends PHPUnit_Framework_TestCase {
    use Test\AssertTranspilesTrait;

    public function testBasicCallable() {
       $bc = "function f(callable \$x)\n{\n}";
       $this->assertTranspiles($bc, 'function f((function()) $x) {}');
       $this->assertTranspiles($bc, 'function f((function()) $x) {}');
       $this->assertTranspiles($bc, 'function f((function(int)) $x) {}');
       $this->assertTranspiles($bc, 'function f((function(int,string,array<foo>)) $x) {}');
       $this->assertTranspiles($bc, 'function f(callable $x) {}');
    }
}
