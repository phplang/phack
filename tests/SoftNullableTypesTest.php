<?php

use PhpLang\Phack\Test;

class PhackSoftNullableTypeTest extends PHPUnit\Framework\TestCase {
    use Test\AssertTranspilesTrait;

    public function testBasicSoftNullable() {
        $sn = "function f( \$x)\n{\n}";
        $this->assertTranspiles(
            '/** * @param ?int $x */ function f( $x) { }',
            'function f(?int $x) {}'
        );
        $this->assertTranspiles(
            '/** * @param Vector<string> $x */ function f( $x) { }',
            'function f(@Vector<string> $x) {}'
        );
        $this->assertTranspiles(
            '/** * @param ?array<array<int>> $x */ function f( $x) { }',
            'function f(?@array<array<int>> $x) {}'
        );
    }
}
