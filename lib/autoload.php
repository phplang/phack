<?php

namespace PhpLang\Phack;
use PhpLang\Phack\PhpParser;

if (defined('PHACK_AUTOLOAD')) {
    // Super experimental, don't enable this unless asked to (yet)
    \PhpLang\Phack\ClassLoader::hijack();
}

/**
 * Convert a HackLang script (open tag required) from HackLang to PHP
 * Allows PHP scripts to passthrough unchanged apart from below:
 * Removes open tag for eval() suitability
 */
function transpileString($str) {
    if (!strncmp($str, '<?php', 5)) {
        return substr($str, 5);
    }
    $ret =
      (new PhpParser\PrettyPrinter\HackLang)
        ->prettyPrint((new PhpParser\ParserFactory)
                         ->create(PhpParser\ParserFactory::HACKLANG)
                         ->parse($str));
    return $ret;
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
