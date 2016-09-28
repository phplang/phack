<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack\Test;

class PhackLambdaTest extends PHPUnit_Framework_TestCase {
    use Test\AssertTranspilesTrait;

    public function testParseLambda() {
        $this->assertTranspiles('$l = function ($x) { return strlen($x); }; var_dump($l("hi"));',
                                '$l = $x ==> strlen($x); var_dump($l("hi"));');
        $this->assertTranspiles('$l = function ($x) { return strlen($x); }; var_dump($l("hi"));',
                                '$l = ($x) ==> strlen($x); var_dump($l("hi"));');
        $this->assertTranspiles('$hyp = function ($a, $b) { return sqrt($a * $a) + sqrt($b * $b); }; var_dump($hyp(3, 4));',
                                '$hyp = ($a, $b) ==> sqrt($a*$a) + sqrt($b*$b); var_dump($hyp(3,4));');
        $this->assertTranspiles('$l = function (string $x) { return strlen($x); }; var_dump($l("hi"));',
                                '$l = (string $x) ==> strlen($x); var_dump($l("hi"));');
    }

    public function testAutoCapture() {
        $this->assertTranspiles('$a = "Hello"; $l = function () use ($a) { return strlen($a); }; var_dump($l());',
                                '$a = "Hello"; $l = () ==> strlen($a); var_dump($l());');
    }

    public function testMultiline() {
        $this->assertTranspiles('$a = "Hello"; $l = function () use ($a) { $b = $a; return strlen($b); }; var_dump($l());',
                                '$a = "Hello"; $l = () ==> { $b = $a; return strlen($b); }; var_dump($l());');
    }
}
