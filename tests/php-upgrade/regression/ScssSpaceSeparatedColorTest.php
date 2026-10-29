<?php
/**
 * #933: a custom SCSS with rgb(0 0 0 / 30%) (CSS Color 4, space separated) made save-custom fail with
 * "TypeError: round(): Argument #1 ($num) must be of type int|float, string given" on PHP 8 (Compiler.php, T_COLOR).
 * The bundled scssphp does not support that syntax: it now raises a compile error that says what to write instead.
 * The comma syntax is unchanged.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/System/polyfills.php';
// The scssphp classes live in lib/Scss under the Leafo\ScssPhp namespace
spl_autoload_register(function ($class) use ($siberian) {
    $prefix = 'Leafo\\ScssPhp\\';
    if (strpos($class, $prefix) === 0) {
        $file = $siberian . '/lib/Scss/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Exception\CompilerException;

$scssCompile = function (string $scss): string {
    $compiler = new Compiler();

    return trim(preg_replace('/\s+/', ' ', $compiler->compile($scss)));
};

return [
    'the comma syntax of the color functions still compiles the same' => function () use ($scssCompile) {
        t_equal('a { color: #000; }', $scssCompile('a{color: rgb(0, 0, 0);}'), 'rgb');
        t_equal('a { color: rgba(0, 0, 0, 0.3); }', $scssCompile('a{color: rgba(0,0,0,.3);}'), 'rgba');
        t_equal('a { color: #0f0; }', $scssCompile('a{color: hsl(120, 100%, 50%);}'), 'hsl');
        t_equal('a { color: rgba(0, 0, 0, 0.5); }', $scssCompile('a{color: rgba(#000, .5);}'), 'rgba(color, alpha)');
    },

    'space separated color arguments raise a compile error instead of a TypeError' => function () use ($scssCompile) {
        foreach (['rgb(0 0 0 / 30%)', 'rgb(0 0 0)', 'rgb(255 128 0 / .5)', 'hsl(120 100% 50% / 50%)'] as $call) {
            $error = null;
            try {
                $scssCompile('a{color: ' . $call . ';}');
            } catch (Throwable $e) {
                $error = $e;
            }
            t_true($error instanceof CompilerException, $call . ': a CompilerException, got ' . ($error ? get_class($error) : 'nothing'));
            t_true(strpos($error->getMessage(), 'comma syntax') !== false, $call . ': the message says what to use');
        }
    },
];
