<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack\Test;

class PhackCtorArgPromotionTest extends PHPUnit_Framework_TestCase {
    use Test\AssertTranspilesTrait;

    public function testParseCtorArgPromotion() {
        $this->assertTranspiles(
            'class C { function __construct($x, $y = "foo") { $this->y = $y; $this->x = $x; } '.
                'public $x; protected $y; }',
            'class C { function __construct(public $x, protected $y = "foo") {} }'
        );
    }
}
