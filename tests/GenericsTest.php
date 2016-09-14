<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackGenericsTest extends PHPUnit_Framework_TestCase {

    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testParseGenerics() {
        $this->assertTranspiles(array(
            'class Foo<T> {}' => "class Foo\n{\n}",
            'class Foo<A, B as Bar> {}' => "class Foo\n{\n}",
            'class Foo<T> { public function bar(): T {}}' =>
                "class Foo\n{\n    public function bar()\n    {\n    }\n}",
            'function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}' =>
                "function foo(\$key, \$val)\n{\n}",
            'class Foo<T> { public T $data; }' =>
                "class Foo\n{\n    public \$data;\n}",
        ));
    }
}
