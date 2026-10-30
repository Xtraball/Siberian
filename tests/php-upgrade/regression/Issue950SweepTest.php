<?php
/**
 * #950: PHP 8.4/8.5 mechanical sweep.
 *  1. the first-party tree has no (boolean)/(integer)/(double) casts, no implicitly nullable parameters and no
 *     `case X;` left (tests/php-upgrade/php85-sweep.php --check says what is left)
 *  2. the sweep itself rewrites those and nothing else
 *  3. dynamic properties of the Zend view/form classes are allowed, Cron\Human starts with an array, Request no
 *     longer calls curl_close()
 */
$root = dirname(__DIR__, 3);
require_once dirname(__DIR__) . '/php85-sweep.php';

return [
    '950 nothing is left to rewrite in the first-party tree' => function () use ($root) {
        $found = php85_sweep_findings($root, php85_sweep_files($root));
        $first = array_slice($found, 0, 5, true);
        t_equal([], $first, 'files still using the old forms (first five)');
    },

    '950 the sweep rewrites casts, nullable parameters and case semicolons' => function () {
        $source = <<<'PHP'
<?php
function f(Foo $a = null, array &$b = null, int|string $c = null, $d = null, ?Bar $e = null, mixed $g = null) {
    $x = (boolean) $a; $y = (INTEGER)$b; $z = (double) $c;
    switch ($x) { case 1; return 1; case $x ? 2 : 3; return 2; default: return 0; }
}
class K { public function __construct(private Baz $p = null, public readonly Q $q = null, callable $cb = null) {} }
PHP;
        [$new, $count] = php85_sweep_source($source);
        t_equal(['cast' => 3, 'nullable' => 6, 'case' => 2], $count, 'counts');
        t_true(strpos($new, 'f(?Foo $a = null, ?array &$b = null, int|string|null $c = null, $d = null, ?Bar $e = null, mixed $g = null)') !== false, 'plain, by-ref and union parameters');
        t_true(strpos($new, 'private ?Baz $p = null, public readonly ?Q $q = null, ?callable $cb = null') !== false, 'promoted and callable parameters');
        t_true(strpos($new, '(bool) $a; $y = (int)$b; $z = (float) $c;') !== false, 'casts');
        t_true(strpos($new, 'case 1: return 1; case $x ? 2 : 3: return 2;') !== false, 'case terminators');
        t_equal(['cast' => 0, 'nullable' => 0, 'case' => 0], php85_sweep_source($new)[1], 'a second pass has nothing left to do');
    },

    '950 the sweep leaves strings, comments, enum cases and other defaults alone' => function () {
        $source = <<<'PHP'
<?php
// (boolean) in a comment, function g(Foo $a = null)
$s = "(integer) and case 1; and Foo \$a = null";
enum Suit { case Hearts; case Spades; }
function h(Foo $a = new Foo, string $b = 'null') { return (bool) $a; }
PHP;
        [$new, $count] = php85_sweep_source($source);
        t_equal($source, $new, 'unchanged');
        t_equal(['cast' => 0, 'nullable' => 0, 'case' => 0], $count, 'nothing counted');
    },

    '950 dynamic properties are allowed on the Zend view, form element and decorator bases' => function () use ($root) {
        foreach (['lib/Zend/View/Abstract.php', 'lib/Zend/Form/Element.php', 'lib/Zend/Form/Decorator/Abstract.php'] as $file) {
            $source = file_get_contents($root . '/siberian/' . $file);
            t_true(
                preg_match('/#\[\\\\AllowDynamicProperties\]\s*\n(abstract )?class /', $source) === 1,
                $file . ' carries #[\AllowDynamicProperties]'
            );
        }
    },

    '950 Cron Human keeps its translations in an array from the start' => function () use ($root) {
        $source = file_get_contents($root . '/siberian/lib/Siberian/Cron/Human.php');
        t_true(strpos($source, 'private $_lang = [];') !== false, 'array, not false');
        t_true(strpos($source, 'private $_lang = FALSE;') === false, 'no false left');
    },

    '950 Request does not call curl_close' => function () use ($root) {
        $source = file_get_contents($root . '/siberian/lib/Siberian/Request.php');
        t_equal(0, substr_count($source, 'curl_close('), 'curl_close() calls');
    },
];
