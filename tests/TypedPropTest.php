<?php

use PhpLang\Phack\Test;

class PhackTypedPropTest extends PHPUnit\Framework\TestCase {
    use Test\AssertTranspilesTrait;

    public function testUntyped() {
        $this->assertTranspiles('class C { private $x; private $y; }',
                                'class C { private $x, $y; }');
    }

    public function testPartialTyped() {
        $this->assertTranspiles('class C { private $x = 1; /** @var int */ private $y; }',
                                'class C { private $x = 1, int $y; }');
    }

    public function testFullyTyped() {
        $this->assertTranspiles('class C { /** @var int */ private $x = 2; /** @var string */ private $y; }',
                                'class C { private int $x = 2, string $y; }');
    }

    public function testFullyTypedArray() {
        $this->assertTranspiles('class C { /** @var array<int, string> */ private $x = 2; /** @var array<int> */ private $y; }',
                                'class C { private array<int, string> $x = 2, array<int> $y; }');
    }
}
