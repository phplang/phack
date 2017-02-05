<?php

use PhpLang\Phack\Test;

class PhackCtorArgPromotionTest extends PHPUnit\Framework\TestCase {
    use Test\AssertTranspilesTrait;

    public function testParseCtorArgPromotion() {
        $this->assertTranspiles(
            'class C { public $x; protected $y; function __construct($x, $y = "foo") { $this->y = $y; $this->x = $x; } }',
            'class C { function __construct(public $x, protected $y = "foo") {} }'
        );
    }
}
