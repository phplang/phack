<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackShiftTest extends PHPUnit_Framework_TestCase {

    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testShift() {
        $this->assertTranspiles(array(
            'echo $a << 123;' => 'echo $a << 123;',
            'echo $a >> 123;' => 'echo $a >> 123;',
            '$a <<= 123;' => '$a <<= 123;',
            '$a >>= 123;' => '$a >>= 123;',
        ));
    }
}
