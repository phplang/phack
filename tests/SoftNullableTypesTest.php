<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack\Test;

class PhackSoftNullableTypeTest extends PHPUnit\Framework\TestCase {
    use Test\AssertTranspilesTrait;

    public function testBasicSoftNullable() {
       $sn = "function f( \$x)\n{\n}";
       $this->assertTranspiles('function f(?int $x) { }', 'function f(?int $x) {}');
       $this->assertTranspiles($sn, 'function f(@Vector<string> $x) {}');
       $this->assertTranspiles($sn, 'function f(?@array<array<int>> $x) {}');
    }
}
