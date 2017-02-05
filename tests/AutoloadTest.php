<?php

use PhpLang\Phack;

class PhackAutoloadTest extends PHPUnit\Framework\TestCase {
    public function testParseHackLang() {
        Phack\ClassLoader::hijack();
        $t = new Phack\Test\Value(42);
        $this->assertEquals($t->get(), 42);
    }
}
