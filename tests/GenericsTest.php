<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackGenericsTest extends PHPUnit_Framework_TestCase {
    use Phack\Test\AssertTranspilesTrait;

    public function testClassGenerics() {
        $this->assertTranspiles('class Foo { }', 'class Foo<T> {}');
        $this->assertTranspiles('class Foo { public $data; }', 'class Foo<T> { public T $data; }');
        $this->assertTranspiles('class Foo { }', 'class Foo<A, B as Bar> {}');
        $this->assertTranspiles('class Foo { public function bar() { } }',
                                'class Foo <T> { public function bar(): T {}}');
    }

    public function testFunctionGenerics() {
        $this->assertTranspiles('function foo($key, $val) { }',
                                'function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}');
    }

    public function testMethodGenerics() {
        $this->assertTranspiles('class C { function foo($key, $val) { } }',
                                'class C { function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}}');
    }

    public function testArgGenerics() {
        $this->assertTranspiles('function foo(ImmSet $set) { }', 'function foo(ImmSet<string> $set) {}');
        $this->assertTranspiles('function bar() : Map { }', 'function bar(): Map<int> {}');
        $this->assertTranspiles('function baz(array $map) : array { }',
                                'function baz(array<string,int> $map): array<int,string> {}');
    }

    public function testNestedGenerics() {
        $this->assertTranspiles('function f(ConstMap $sets) { }',
                                'function f(ConstMap<string, ConstSet<int> > $sets) {}');
        $this->assertTranspiles('function f(ConstMap $sets) { }',
                                'function f(ConstMap<string, ConstSet<int>> $sets) {}');
        $this->assertTranspiles('function f(ConstVector $sets) { }',
                                'function f(ConstVector<ConstMap<ConstSet<int>, string>> $sets) {}');
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
