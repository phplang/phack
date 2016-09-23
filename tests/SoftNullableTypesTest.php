<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackSoftNullableTypeTest extends PHPUnit_Framework_TestCase {
    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testBasicSoftNullable() {
       $sn = "function f( \$x)\n{\n}";
       $this->assertTranspiles(array(
           'function f(?int $x) {}' => $sn,
           'function f(@Vector<string> $x) {}' => $sn,
           'function f(?@array<array<int>> $x) {}' => $sn,
       ));
    }
}
