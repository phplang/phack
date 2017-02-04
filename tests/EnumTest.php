<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackEnumTest extends PHPUnit\Framework\TestCase {
    public function testEnum() {
       Phack\includeFile(__DIR__.'/MyEnum.php');
       $this->assertTrue(class_exists(Phack\Test\MyEmptyEnum::class));
       $this->assertTrue(class_exists(Phack\Test\MyEnum::class));
       $this->assertTrue(class_exists(Phack\Test\MyStringEnum::class));
       $this->assertEquals(1, Phack\Test\MyEnum::ONE);
       $this->assertEquals(Phack\Test\MyEnum::TWO, Phack\Test\MyEnum::coerce(2));
       $this->assertEquals(null, Phack\Test\MyEnum::coerce(-1));
       $this->assertEquals(array('ONE' => 1, 'TWO' => 2), Phack\Test\MyEnum::getValues());
       $this->assertEquals(array('FOO' => 'bar', 'BAZ' => 'qux'), Phack\Test\MyStringEnum::getValues());
    }
}
