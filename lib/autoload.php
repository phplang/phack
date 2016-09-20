<?php

namespace PhpLang\Phack;
use PhpLang\Phack\PhpParser;

if (defined('PHACK_AUTOLOAD')) {
    // Super experimental, don't enable this unless asked to (yet)
    \PhpLang\Phack\ClassLoader::hijack();
}

/**
 * Parse and compile a HackLAng string (open tag required) into an AST tree
 */
function compileString($str) {
    if (!strncmp($str, '<?php', 5)) {
        return (new \PhpParser\ParserFactory)
            ->create(\PhpParser\ParserFactory::PREFER_PHP7)
            ->parse($str);
    } else {
        return (new PhpParser\ParserFactory)
            ->create(PhpParser\ParserFactory::HACKLANG)
            ->parse($str);
    }
}

/**
 * Convert a HackLang script (open tag required) from HackLang to PHP
 * Allows PHP scripts to passthrough unchanged apart from below:
 * Removes open tag for eval() suitability
 */
function transpileString($str) {
    return (new PhpParser\PrettyPrinter\HackLang)->prettyPrint(compileString($str));
}

/**
 * Eval a HackLang string without opening tags
 */
function evalString($str) {
  return eval(transpileString("<?hh $str"));
}

function includeFile($filename) {
  return eval(transpileString(file_get_contents($filename)));
}
