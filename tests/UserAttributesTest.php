<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackUserAttributesTest extends PHPUnit\Framework\TestCase {
    use Phack\Test\AssertTranspilesTrait;

    private function assertUAon($pattern, $cb) {
        $ast = Phack\compileString(sprintf('<?hh '.$pattern,
            '<<Foo, Bar("Baz", "Blong" => "Qux")>><<Fizz\Buzz>>'));
        $attrs = $cb($ast)->user_attributes;
        $this->assertEquals(3, count($attrs));
        $this->assertEquals('Foo', $attrs[0]->name->toString());
        $this->assertEquals(0, count($attrs[0]->values));
        $this->assertEquals('Bar', $attrs[1]->name->toString());
        $this->assertEquals(2, count($attrs[1]->values));
        $this->assertNull($attrs[1]->values[0]->key);
        $this->assertEquals('Baz', $attrs[1]->values[0]->value->value);
        $this->assertEquals('Blong', $attrs[1]->values[1]->key->value);
        $this->assertEquals('Qux', $attrs[1]->values[1]->value->value);
        $this->assertEquals('Fizz\\Buzz', $attrs[2]->name->toString());
        $this->assertEquals(0, count($attrs[2]->values));
    }

    public function testUAFunctions() {
        $this->assertTranspiles('function foo() { }', '<<Foo>>function foo() {}');
        $this->assertUAon('%s function f() {}', function($ast) { return $ast[0]; });
    }

    public function testUAClasses() {
        $this->assertTranspiles('class Bar { }', '<<Foo>>class Bar {}');
        $this->assertTranspiles('class Bar { public function Qux() { } }',
                                '<<Foo>>class Bar { <<Baz>>public function Qux() {} }');
        $this->assertUAon('%s class C {}', function ($ast) { return $ast[0]; });
        $this->assertUAon('class C { %s public function D() {} }',
            function ($ast) { return $ast[0]->stmts[0]; });
    }
}
