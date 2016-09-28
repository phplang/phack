<?php

namespace PhpLang\Phack\Test;
use PhpLang\Phack;

trait AssertTranspilesTrait {
    private function assertTranspiles($php, $hack) {
       $normalize = function ($str) { return preg_replace('/\s+/', ' ', str_replace("\n", ' ', $str)); };
       $transpiled = Phack\transpileString('<?hh ' . $hack);
       $this->assertEquals($normalize($php), $normalize($transpiled));
    }
}
