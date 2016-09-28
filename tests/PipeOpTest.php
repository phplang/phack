<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackPipeOpTest extends PHPUnit_Framework_TestCase {
    use Phack\Test\AssertTranspilesTrait;

    public function testBasicPipe() {
       $this->assertTranspiles('$x;', '$x |> $$;');
       $this->assertTranspiles('foo($x);', '$x |> foo($$);');
       $this->assertTranspiles('$x . $y;', '$x |> $$ . $y;');
       $this->assertTranspiles('c(b(a($x)));', '$x |> a($$) |> b($$) |> c($$);');
    }

    public function testWrongPipeCount() {
        try {
            Phack\transpileString('<?hh $$;');
            $this->assertTrue(false);
        } catch (\Exception $e) {}
        try {
            Phack\transpileString('<?hh $x |> $$.$$;');
            $this->assertTrue(false);
        } catch (\Exception $e) {}
        $this->assertTrue(true);
    }

    public function testNestedPipes() {
       $this->assertTranspiles('c(b(a($x)));', '$x |> (a($$) |> b($$)) |> c($$);');
    }
}
