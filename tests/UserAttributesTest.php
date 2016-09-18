<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackUserAttributesTest extends PHPUnit_Framework_TestCase {
    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testUAFunctions() {
        $this->assertTranspiles(array(
            '<<Foo>>function foo() {}' =>
                "function foo()\n{\n}",
        ));
    }

    public function testUAClasses() {
        $this->assertTranspiles(array(
            '<<Foo>>class Bar {}' =>
                "class Bar\n{\n}",
        ));
    }
}
