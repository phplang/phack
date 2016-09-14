<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackBasicTest extends PHPUnit_Framework_TestCase {
    public function testParseHackLang() {
       $this->assertEquals('echo "Hello World";', Phack\transpileString('<?hh echo "Hello World";'));
    }
}
