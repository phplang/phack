<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpLang\Phack;

class PhackLambdaTest extends PHPUnit_Framework_TestCase {

    private function assertTranspiles(array $map) {
       foreach ($map as $hack => $php) {
           $this->assertEquals($php, Phack\transpileString("<?hh $hack"));
       }
    }

    public function testParseLambda() {
        $this->assertTranspiles(array(
            '$l = $x ==> strlen($x); var_dump($l("hi"));' =>
                '$l = function ($x) { return strlen($x); };'.PHP_EOL.
                'var_dump($l("hi"));',
            '$l = ($x) ==> strlen($x); var_dump($l("hi"));' =>
                '$l = function ($x) { return strlen($x); };'.PHP_EOL.
                'var_dump($l("hi"));',
            '$hyp = ($a, $b) ==> sqrt($a*$a) + sqrt($b*$b); var_dump($hyp(3,4));' =>
                '$hyp = function ($a, $b) { return sqrt($a * $a) + sqrt($b * $b); };'.PHP_EOL.
                'var_dump($hyp(3, 4));',
            '$l = (string $x) ==> strlen($x); var_dump($l("hi"));' =>
                '$l = function ($x) { return strlen($x); };'.PHP_EOL.
                'var_dump($l("hi"));',
        ));
    }

    public function testAutoCapture() {
        $this->assertTranspiles(array(
            '$a = "Hello"; $l = () ==> strlen($a); var_dump($l());' =>
                '$a = "Hello";'.PHP_EOL.
                '$l = function () use ($a) { return strlen($a); };'.PHP_EOL.
                'var_dump($l());',
        ));
    }

    public function testMultiline() {
        $this->assertTranspiles(array(
            '$a = "Hello"; $l = () ==> { $b = $a; return strlen($b); }; var_dump($l());' =>
                '$a = "Hello";'.PHP_EOL.
                '$l = function () use ($a) {'.PHP_EOL.
                '    $b = $a;'.PHP_EOL.
                '    return strlen($b);'.PHP_EOL.
                '};'.PHP_EOL.
                'var_dump($l());',
        ));
    }
}
