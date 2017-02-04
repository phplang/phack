<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackGenericsTest extends PHPUnit\Framework\TestCase {
    use Phack\Test\AssertTranspilesTrait;

    public function testClassGenerics() {
        $this->assertTranspiles('/** * @template T */ class Foo { }', 'class Foo<T> {}');
        $this->assertTranspiles('/** * @template T */ class Foo { /** @var T */ public $data; }', 'class Foo<T> { public T $data; }');
        $this->assertTranspiles('/** * @template A * @template B as Bar */ class Foo { }', 'class Foo<A, B as Bar> {}');
        $this->assertTranspiles('/** * @template T */ class Foo { /** @return T */ public function bar() { } }',
                                'class Foo <T> { public function bar(): T {}}');
    }

    public function testFunctionGenerics() {
        $this->assertTranspiles('/** * @template Tk * @template Tv * @return Tv */ function foo($key, $val) { }',
                                'function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}');
        $this->assertTranspiles('/** * @template T as Foo * @param T */ function foo($x) { }',
                                'function foo<T as Foo>(T $x) {}');
        $this->assertTranspiles('/** * @template T super Foo * @param T */ function foo($x) { }',
                                'function foo<T super Foo>(T $x) {}');
    }

    public function testMethodGenerics() {
        $this->assertTranspiles('class C { /** * @template Tk * @template Tv * @return Tv */ function foo($key, $val) { } }',
                                'class C { function foo<Tk,Tv>(Tk $key, Tv $val): Tv {}}');
    }

    public function testArgGenerics() {
        $this->assertTranspiles(
            '/** * @param ImmSet<string> $set */ function foo(ImmSet $set) { }',
            'function foo(ImmSet<string> $set) {}'
        );

        $this->assertTranspiles(
            '/** * @return Map<int> */ function bar() : Map { }',
            'function bar(): Map<int> {}'
        );

        $this->assertTranspiles(
            '/** * @param array<string, int> $map * @return array<int, string> */ function baz(array $map) : array { }',
            'function baz(array<string,int> $map): array<int,string> {}'
        );
    }

    public function testNestedGenerics() {
        $this->assertTranspiles(
            '/** * @param ConstMap<string, ConstSet<int>> $sets */ function f(ConstMap $sets) { }',
            'function f(ConstMap<string, ConstSet<int> > $sets) {}'
        );

        $this->assertTranspiles(
            '/** * @param ConstMap<string, ConstSet<int>> $sets */ function f(ConstMap $sets) { }',
            'function f(ConstMap<string, ConstSet<int>> $sets) {}'
        );

        $this->assertTranspiles(
            '/** * @param ConstVector<ConstMap<ConstSet<int>, string>> $sets */ function f(ConstVector $sets) { }',
            'function f(ConstVector<ConstMap<ConstSet<int>, string>> $sets) {}'
        );
    }

    public function testParseSubtypes() {
        $tree = Phack\compileString('<?hh function f(X<Y> $z, A\\B<C\\D> $e) {}');

        $param = $tree[0]->params[0];
        $this->assertInstanceOf(Phack\PhpParser\Node\GenericsType::class, $param->type);
        $this->assertInstanceOf(\PhpParser\Node\Name::class, $param->type->basetype);
        $this->assertEquals('X', $param->type->basetype->toString());
        $this->assertInstanceOf(\PhpParser\Node\Name::class, $param->type->subtypes[0]);
        $this->assertEquals('Y', $param->type->subtypes[0]->toString());

        $param = $tree[0]->params[1];
        $this->assertInstanceOf(Phack\PhpParser\Node\GenericsType::class, $param->type);
        $this->assertInstanceOf(\PhpParser\Node\Name::class, $param->type->basetype);
        $this->assertEquals('A\\B', $param->type->basetype->toString());
        $this->assertEquals('C\\D', $param->type->subtypes[0]->toString());
    }
}
