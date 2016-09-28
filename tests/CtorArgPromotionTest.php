<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackCtorArgPromotionTest extends PHPUnit_Framework_TestCase {

    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testParseCtorArgPromotion() {
        $this->assertTranspiles(array(
            'class C { function __construct(public $x, protected $y = "foo") {} }' =>
                "class C\n{\n    function __construct(\$x, \$y = \"foo\")\n".
                "    {\n        \$this->y = \$y;\n        \$this->x = \$x;\n    }\n".
                "    public \$x;\n    protected \$y;\n}",
        ));
    }
}
