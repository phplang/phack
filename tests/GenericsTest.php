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
                "function foo(ImmSet \$set)\n{\n}",
            'function bar(): Map<int> {}' =>
                "function bar() : Map\n{\n}",
            'function baz(array<string,int> $map): array<int,string> {}' =>
                "function baz(array \$map) : array\n{\n}",
        ));
    }

    public function testNestedGenerics() {
        $this->assertTranspiles(array(
            'function f(ConstMap<string, ConstSet<int> > $sets) {}' =>
                "function f(ConstMap \$sets)\n{\n}",
            'function f(ConstMap<string, ConstSet<int>> $sets) {}' =>
                "function f(ConstMap \$sets)\n{\n}",
            'function f(ConstVector<ConstMap<ConstSet<int>, string>> $sets) {}' =>
                "function f(ConstVector \$sets)\n{\n}",
        ));
    }

    public function testParseSubtypes() {
        $tree = Phack\compileString('<?hh function f(X<Y> $z, A\\B<C\\D as E\\F> $g) {}');

        $param = $tree[0]->params[0];
        $this->assertInstanceOf(Phack\PhpParser\Node\GenericsType::class, $param->type);
        $this->assertInstanceOf(\PhpParser\Node\Name::class, $param->type->basetype);
        $this->assertEquals('X', implode('\\', $param->type->basetype->parts));
        $this->assertInstanceOf(\PhpParser\Node\Name::class, $param->type->subtypes[0]);
        $this->assertEquals('Y', implode('\\', $param->type->subtypes[0]->parts));

        $param = $tree[0]->params[1];
        $this->assertInstanceOf(Phack\PhpParser\Node\GenericsType::class, $param->type);
        $this->assertInstanceOf(\PhpParser\Node\Name::class, $param->type->basetype);
        $this->assertEquals('A\\B', implode('\\', $param->type->basetype->parts));
        $this->assertInstanceOf(Phack\PhpParser\Node\GenericsTypeAs::class, $param->type->subtypes[0]);
        $this->assertEquals('C\\D', implode('\\', $param->type->subtypes[0]->name->parts));
        $this->assertEquals('E\\F', implode('\\', $param->type->subtypes[0]->as->parts));
    }
}
