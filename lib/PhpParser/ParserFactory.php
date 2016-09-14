<?php

namespace PhpLang\Phack\PhpParser;

class ParserFactory extends \PhpParser\ParserFactory {
    const HACKLANG = 'hacklang';

    public function create($kind, \PhpParser\Lexer $lexer = null, array $options = array()) {
        if ($kind !== self::HACKLANG) {
            return parent::create($kind, $lexer, $options);
        }
        if ($lexer === null) {
            $lexer = new Lexer\HackLang;
        }
        return new Parser\HackLang($lexer, $options);
    }
}
