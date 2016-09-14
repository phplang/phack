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
}
