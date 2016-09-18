<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackUserAttributesTest extends PHPUnit_Framework_TestCase {
    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testParseHackLang() {
        $this->assertTranspiles(array(
            '<<Foo>>function foo() {}' =>
                "function foo()\n{\n}",
        ));
    }
}
