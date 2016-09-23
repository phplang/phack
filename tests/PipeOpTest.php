<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackPipeOpTest extends PHPUnit_Framework_TestCase {
    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testBasicPipe() {
       $this->assertTranspiles(array(
           '$x |> $$;' => '$x;',
           '$x |> foo($$);' => 'foo($x);',
           '$x |> $$ . $y;' => '$x . $y;',
           '$x |> a($$) |> b($$) |> c($$);' => 'c(b(a($x)));',
       ));
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
       $this->assertTranspiles(array(
           '$x |> (a($$) |> b($$)) |> c($$);' => 'c(b(a($x)));',
       ));
    }
}
