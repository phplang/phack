<?php

namespace PhpLang\Phack\PhpParser\Lexer;
use PhpLang\Phack\PhpParser\Parser\Tokens;

class HackLang extends \PhpParser\Lexer\Emulative {
    const T_LAMBDA_ARROW = 2000;
    const T_LAMBDA_OP = 2001;
    const T_LAMBDA_CP = 2002;
    const T_ENUM = 2003;
    const T_PIPE = 2004;
    const T_PIPE_VAR = 2005;
    const T_SUPER = 2006;

    public function __construct(array $options = array()) {
        parent::__construct($options);

        $this->tokenMap[self::T_LAMBDA_ARROW] = Tokens::T_LAMBDA_ARROW;
        $this->tokenMap[self::T_LAMBDA_OP]    = Tokens::T_LAMBDA_OP;
        $this->tokenMap[self::T_LAMBDA_CP]    = Tokens::T_LAMBDA_CP;
        $this->tokenMap[self::T_ENUM]         = Tokens::T_ENUM;
        $this->tokenMap[self::T_PIPE]         = Tokens::T_PIPE;
        $this->tokenMap[self::T_PIPE_VAR]     = Tokens::T_PIPE_VAR;
        $this->tokenMap[self::T_SUPER]        = Tokens::T_SUPER;
    }

    /*
     * Copypasta of \PhpParser\Lexer's createTokenMap except for the defined() statement
     * Everything else should be the same
     */
    protected function createTokenMap() {
        $tokenMap = array();

        // 256 is the minimum possible token number, as everything below
        // it is an ASCII value
        for ($i = 256; $i < 1000; ++$i) {
            if (T_DOUBLE_COLON === $i) {
                // T_DOUBLE_COLON is equivalent to T_PAAMAYIM_NEKUDOTAYIM
                $tokenMap[$i] = Tokens::T_PAAMAYIM_NEKUDOTAYIM;
            } elseif(T_OPEN_TAG_WITH_ECHO === $i) {
                // T_OPEN_TAG_WITH_ECHO with dropped T_OPEN_TAG results in T_ECHO
                $tokenMap[$i] = Tokens::T_ECHO;
            } elseif(T_CLOSE_TAG === $i) {
                // T_CLOSE_TAG is equivalent to ';'
                $tokenMap[$i] = ord(';');
            } elseif ('UNKNOWN' !== $name = token_name($i)) {
                if ('T_HASHBANG' === $name) {
                    // HHVM uses a special token for #! hashbang lines
                    $tokenMap[$i] = Tokens::T_INLINE_HTML;
                } else if (defined($name = 'PhpLang\Phack\PhpParser\Parser\Tokens::' . $name)) {
                    // Other tokens can be mapped directly
                    $tokenMap[$i] = constant($name);
                }
            }
        }

        // HHVM uses a special token for numbers that overflow to double
        if (defined('T_ONUMBER')) {
            $tokenMap[T_ONUMBER] = Tokens::T_DNUMBER;
        }
        // HHVM also has a separate token for the __COMPILER_HALT_OFFSET__ constant
        if (defined('T_COMPILER_HALT_OFFSET')) {
            $tokenMap[T_COMPILER_HALT_OFFSET] = Tokens::T_STRING;
        }

        return $tokenMap;
    }

    /**
     * Scans back from the current token position to try to find
     * a pattern like:  < (T_ARRAY|T_STRING|T_AS)* >>
     * Which indicated we're probably dealing with nested generics.
     * Otherwise we have a single shift-right
     */
    private function isProbableNestedGenericExpressionEnd(array $tokens, $pos) {
        for ($i = $pos - 1; $i > 0; --$i) {
            if (!isset($tokens[$i])) return false;
            $token = $tokens[$i];
            if (($token === '<') || ($token === '>')) return true;
            if ($token === ',') continue;
            if (!is_array($token)) return false;
            if (   (T_ARRAY      !== $token[0])
                && (T_STRING     !== $token[0])
                && (T_AS         !== $token[0])
                && (T_WHITESPACE !== $token[0])) return false;
        }

        // If we exit the for loop, it's because we ran out of tokens scanning back
        return false;
    }

    /**
     * Scans back from the current T_LAMBDA_ARROW token to translate
     * any enclosing parameter list parentheses into T_LAMBA_OP/CP to
     * avoid a parser shift/reduce conflict
     */
    private function fixupLambdaParens($pos) {
        assert(isset($this->tokens[$pos][2]));
        for ($i = $pos - 1; ($this->tokens[$i][0]??null) === T_WHITESPACE; --$i);
        if (!isset($this->tokens[$i])) return;
        if ($this->tokens[$i] !== ')') return;
        $cp = $i;
        $nest = 0;
        $line = $this->tokens[$pos][2];
        for (--$i; isset($this->tokens[$i]); --$i) {
            $line = $this->tokens[$i][2] ?: $line;
            if ($this->tokens[$i] === ')') { ++$nest; continue; }
            if ($this->tokens[$i] === '(') {
                if (!$nest--) {
                    /* Found matching paren */
                    $this->tokens[$i] = array(self::T_LAMBDA_OP, '(', $line);
                    $this->tokens[$cp] = array(self::T_LAMBDA_CP, ')', $this->tokens[$pos][2]);
                    return;
                }
            }
        }
    }

    private static function isCastToken($token) {
        if (!isset($token[0])) return false;
        $t = $token[0];
        return (($t === T_BOOL_CAST)
            || ($t === T_INT_CAST)
            || ($t === T_DOUBLE_CAST)
            || ($t === T_STRING_CAST)
            || ($t === T_ARRAY_CAST)
            || ($t === T_OBJECT_CAST));
    }

    private static function isProbableFalseCastToken(array $tokens, $pos) {
        for (--$pos; isset($tokens[$pos]); --$pos) {
            $t = $tokens[$pos];
            if (($t !== '&') && !(isset($t[0]) && ($t[0] === T_WHITESPACE))) break;
        }
        return isset($tokens[$pos][0])
            && ($tokens[$pos][0] === T_FUNCTION);
    }

    private function preprocessOpenTag($code) {
        // Special handling of open tag with special special handling of Shebangs
        $lines = explode("\n", $code, 3);
        $firstline = array_shift($lines);
        $shebang = '';
        if (!strncmp($firstline, '#!', 2)) {
            $shebang = $firstline . "\n";
            $firstline = array_shift($lines);
        }
        if (strncmp($firstline, '<?hh', 4)) {
            throw new \RuntimeException('HackLang files must begin with <?hh');

        }
        return $shebang . '<?php' . substr($firstline, 4) . "\n" .
               implode("\n", $lines);
    }

    protected function requiresEmulation($code) {
        return true;
    }

    protected function emulateTokens() {
        // Copypasta from base Lexer\Emulative
        // Deal with our rewrites first, since parent will panic on unknown rewrite
        for ($i = 0, $c = count($this->tokens); $i < $c; ++$i) {
            // For translating single-char-only tokens to a multi-char token w/ lineno
            if (isset($this->tokens[$i][2])) {
                $lastline = $this->tokens[$i][2];
            }
            // first check that the following tokens are of form ~LABEL~,
            // then match the __EMU__... sequence.
            if (T_IS_EQUAL === $this->tokens[$i][0]
                && isset($this->tokens[$i + 1])
                && '>' === $this->tokens[$i + 1]
            ) {
                array_splice($this->tokens, $i, 2, array(
                    [self::T_LAMBDA_ARROW, '==>', $this->tokens[$i][2]],
                ));
                $c -= 2;
                $this->fixupLambdaParens($i);

            // second, change `enum` strings to T_ENUM
            } elseif (is_array($this->tokens[$i])
                && T_STRING === $this->tokens[$i][0]
                && !strcasecmp('enum', $this->tokens[$i][1])
            ) {
                $this->tokens[$i][0] = self::T_ENUM;

            // Replace `super` strings to T_SUPER
            } elseif (is_array($this->tokens[$i])
                && T_STRING === $this->tokens[$i][0]
                && !strcasecmp('super', $this->tokens[$i][1])
            ) {
                $this->tokens[$i][0] = self::T_SUPER;

            // Change T_CAST_* to '(' T_STRING ')' as needed
            // to avoid callable typehints being misparsed:
            // e.g. function f((function(int):bool) $x) {}
            } elseif (is_array($this->tokens[$i])
                && self::isCastToken($this->tokens[$i])
                && self::isProbableFalseCastToken($this->tokens, $i)) {
                array_splice($this->tokens, $i, 1, array(
                    '(',
                    array(
                        T_STRING,
                        trim($this->tokens[$i][1], '()'),
                        $this->tokens[$i][2],
                    ),
                    ')',
                ));
                $c += 2;

            // Translate >> to > and >
            // to allow nested generics
            } elseif (is_array($this->tokens[$i])
                && ($this->tokens[$i][0] === T_SR)
                && $this->isProbableNestedGenericExpressionEnd($this->tokens, $i)) {
                array_splice($this->tokens, $i, 1, array('>', '>'));
                ++$c;

            // '|' '>' to T_PIPE unconditionally
            } elseif (($this->tokens[$i] === '|')
                && isset($this->tokens[$i+1])
                && ($this->tokens[$i+1] === '>')) {
                array_splice($this->tokens, $i, 2, array(
                    array(self::T_PIPE, '|>', $lastline),
                ));
                --$c;

            // '$' '$' to T_PIPE_VAR unconditionally
            } elseif (($this->tokens[$i] === '$')
                && isset($this->tokens[$i+1])
                && ($this->tokens[$i+1] === '$')) {
                array_splice($this->tokens, $i, 2, array(
                    array(self::T_PIPE_VAR, '$$', $lastline),
                ));
                --$c;

            // finally, replace a short open tag followed by `hh`
            // with a long open tag.
            } elseif (is_array($this->tokens[$i])
                && T_OPEN_TAG === $this->tokens[$i][0]
                && '<?' === $this->tokens[$i][1]
                && isset($this->tokens[$i + 1])
                && is_array($this->tokens[$i + 1])
                && T_STRING === $this->tokens[$i + 1][0]
                && 'hh' == $this->tokens[$i + 1][1]
            ) {
                array_splice($this->tokens, $i, 2, array(
                    array(T_OPEN_TAG, '<?hh', $this->tokens[$i + 1][2]),
                ));
                --$c;
            }
        }
        parent::emulateTokens();
    }

    public function restoreContentCallback(array $matches) {
        if ('LAMBDAARROW' === $matches[1]) {
            return '==>';
        } else {
            return parent::restoreContentCallback($matches);
        }
    }
}
