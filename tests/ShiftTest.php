<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack\Test;

class PhackShiftTest extends PHPUnit\Framework\TestCase {
    use Test\AssertTranspilesTrait;

    public function testShift() {
        $this->assertTranspiles('echo $a << 123;', 'echo $a << 123;');
        $this->assertTranspiles('echo $a >> 123;', 'echo $a >> 123;');
        $this->assertTranspiles('$a <<= 123;', '$a <<= 123;');
        $this->assertTranspiles('$a >>= 123;', '$a >>= 123;');
    }
}
