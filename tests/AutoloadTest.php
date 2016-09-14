<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackAutoloadTest extends PHPUnit_Framework_TestCase {
    public function testParseHackLang() {
        Phack\ClassLoader::hijack();
        $t = new Phack\Test\Value(42);
        $this->assertEquals($t->get(), 42);
    }
}
