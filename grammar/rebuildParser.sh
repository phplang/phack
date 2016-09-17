#!/bin/sh

cd $(dirname $0)
PHP=${PHP:-$(which php)}
if [ -z "$PHP" ]; then
  echo "php executable not found" >&2
  exit 1
fi

SED=${SED:-$(which sed)}
if [ -z "$SED" ]; then
  echo "sed executable not found" >&2
  exit 1
fi

# There's probably a way to only replace the last %% with hack-extras.y,
# but I can't figure out what it is.  Stick to brute force.
$SED -e 's/%%//g' -e '/%tokens/a %%' -e '$r hack-extras.y' -e '$a %%' \
	< ../vendor/nikic/php-parser/grammar/php7.y \
	> hacklang.y

$SED -e 's/namespace PhpParser\\Parser;/namespace PhpLang\\Phack\\PhpParser\\Parser;/g' \
	-e '/^namespace/a use PhpLang\\Phack\\PhpParser\\Node\\Expr as PhackExpr;' \
	-e '/^namespace/a use PhpLang\\Phack\\PhpParser\\Node\\Stmt as PhackStmt;' \
	-e '/the grammar file/d' -e '/the skeleton file/d' \
	-e 's/rebuildParsers.php/rebuildParser.sh/g' \
	< ../vendor/nikic/php-parser/grammar/parser.template \
	> parser.template

$SED -e 's/namespace PhpParser\\Parser;/namespace PhpLang\\Phack\\PhpParser\\Parser;/g' \
	< ../vendor/nikic/php-parser/grammar/tokens.template \
	> tokens.template

$SED -e '/REQUIRE_ONCE/a %right T_LAMBDA_ARROW' \
     -e '/REQUIRE_ONCE/a %token T_LAMBDA_OP T_LAMBDA_CP' \
     -e '/REQUIRE_ONCE/a %left T_ENUM' \
	< ../vendor/nikic/php-parser/grammar/tokens.y \
	> tokens.y

$SED -e '/Php5/d' -e 's/php7/hacklang/g' -e 's/Php7/HackLang/g' \
	< ../vendor/nikic/php-parser/grammar/rebuildParsers.php \
	> rebuildParser.php

$PHP rebuildParser.php
