# Phack: HackLang to PHP Transpiler

This project is EXTREMELY EXPERIMENTAL and not ready for prime-time.

Only a few HackLang features are supported at this time, and they aren't fully tested.

## What is this?

This project is an attempt to make [HackLang](http://www.hacklang.org) code run "out of the box" on a regular PHP runtime without the need for a custom extension (it's all PHP code!).  By supporting all of HackLang's rich typing system, we'll be able to make use of the static typechecker to perform robust analysis while still executing it on the stable and trusted PHP runtime.

Any attempt to use HackLang files which have not passed `hh_client` is a terrible mistake, and you should feel bad.

## How does it work?

Phack extends [PHP-Parser](https://www.github.com/nikic/PHP-Parser) by amending the PHP 7 parsing rules and overriding the Lexer's pre/post processor hooks.  This produces a usable AST, which can then be "PrettyPrinted" into normal PHP.  The following table shows our roadmap:

| Feature | State | Notes |
| ------- | ----- | ----- |
| [Short Lambdas](https://docs.hhvm.com/hack/lambdas) | Alpha | No support for more than one closure argument, needs more tests |
| [Generics](https://docs.hhvm.com/hack/generics) | Alpha | Full type erasure at runtime |
| [XHP](https://docs.hhvm.com/hack/XHP) | TBD | XHP-1.x support available through https://www.github.com/phplang/xhp for now, XHP-2.x coming with this library |
| [Enums](https://docs.hhvm.com/hack/enums) | Alpha | Need to actually implement \InvariantException for full HackLang compat... |
| [Type Aliasing](https://docs.hhvm.com/hack/type-aliases) | TBD | |
| [Prop/Const Typing](https://docs.hhvm.com/hack/types) | TBD | |
| [Callable typing](https://docs.hhvm.com/hack/types) | TBD | |
| [User Attributes](https://docs.hhvm.com/hack/attributes) | TBD | |
| [Shapes](https://docs.hhvm.com/hack/shapes) | TBD | |
| [Collections](https://docs.hhvm.com/hack/collections) | TBD | Static initialization is... complicated... |
| [Async](https://docs.hhvm.com/hack/async) | Unlikely | PHP's Engine just doesn't support this concept well |

## How do I use it?

To include a HackLang file manually, use `\PhpLang\Phack\includeFile()` as you would use `include`, similarly you can use `\PhpLang\Phack\evalString` as you would `eval`.

To have composer handle HackLang files transparently, you can try out the EXPERIMENTAL replacement `ClassLoader` by invoking, at the top level of your entrypoint: `\PhpLang\Phack\ClassLoader::hijack();`.  After this point, any class autoloaded via composer will be preprocessed from HackLang into runable PHP.

To see how HackLang files transpile to PHP, you can run `vendor/bin/phack` which will invoke `PhpLang\Phack\transpileString` for you and provide the output.
