<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackGenericsTest extends PHPUnit_Framework_TestCase {

    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testClassGenerics() {
        $this->assertTranspiles(array(
            'class Foo<T> {}' => "class Foo\n{\n}",
            'class Foo<T> { public T $data; }' =>
                "class Foo\n{\n    public \$data;\n}",
            'class Foo<A, B as Bar> {}' => "class Foo\n{\n}",
            'class Foo<T> { public function bar(): T {}}' =>
                "class Foo\n{\n    public function bar()\n    {\n    }\n}",
        ));
    }

    public function testFunctionGenerics() {
        $this->assertTranspiles(array(
            'function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}' =>
                "function foo(\$key, \$val)\n{\n}",
        ));
    }

    public function testMethodGenerics() {
        $this->assertTranspiles(array(
            'class C { function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}}' =>
                "class C\n{\n    function foo(\$key, \$val)\n    {\n    }\n}",
        ));
    }

    public function testArgGenerics() {
        $this->assertTranspiles(array(
            'function foo(ImmSet<string> $set) {}' =>
                "function foo(\$set)\n{\n}",
            'function bar(): Map<int> {}' =>
                "function bar()\n{\n}",
            'function baz(array<string,int> $map): array<int,string> {}' =>
                "function baz(\$map)\n{\n}",
        ));
    }

    public function testNestedGenerics() {
        $this->assertTranspiles(array(
            'function f(ConstMap<string, ConstSet<int> > $sets) {}' =>
                "function f(\$sets)\n{\n}",
            'function f(ConstMap<string, ConstSet<int>> $sets) {}' =>
                "function f(\$sets)\n{\n}",
            'function f(ConstVector<ConstMap<ConstSet<int>, string>> $sets) {}' =>
                "function f(\$sets)\n{\n}",
        ));
    }
}
