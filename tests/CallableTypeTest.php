<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackCallableTypeTest extends PHPUnit_Framework_TestCase {
    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testBasicCallable() {
       $bc = "function f(callable \$x)\n{\n}";
       $this->assertTranspiles(array(
           'function f((function()) $x) {}' => $bc,
           'function f((function(int)) $x) {}' => $bc,
           'function f((function(int,string,array<foo>)) $x) {}' => $bc,
           'function f(callable $x) {}' => $bc,
       ));
    }
}
