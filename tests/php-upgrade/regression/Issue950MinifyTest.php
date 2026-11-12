<?php
/**
 * #950: the bundled matthiasmullie/minify on PHP 8.2+.
 *  1. its output is byte for byte what it was before the fix (golden files of real app files, bundles and concat())
 *  2. it raises no deprecation (`'parent'` in a callable, PHP 8.2)
 *  3. Siberian's local patches are still in the bundled copy: replacing it by a stock release loses `concat()`
 *     and changes the output of 15% of the repository's files
 */
$root = dirname(__DIR__, 3);
require_once dirname(__DIR__) . '/tooling/minify-golden.php';

$srcDir = dirname(__DIR__) . '/fixtures/minify/src';
$goldenDir = dirname(__DIR__) . '/fixtures/minify/golden';
$vendor = $root . '/siberian/lib/vendor/matthiasmullie/minify/src';

return [
    '950 minify: every fixture has a golden file and the other way round' => function () use ($srcDir, $goldenDir) {
        $sources = array_map('basename', glob($srcDir . '/*'));
        $golden = array_map('basename', glob($goldenDir . '/*'));
        $expected = array_merge($sources, ['bundle.js', 'bundle.concat.js', 'bundle.css']);
        sort($expected);
        sort($golden);
        t_equal($expected, $golden, 'golden files');
        t_true(count($sources) >= 10, 'enough fixtures');
    },

    '950 minify: the output is byte for byte the golden one' => function () use ($srcDir, $goldenDir) {
        error_reporting(E_ALL & ~E_DEPRECATED); // deprecations have their own test
        foreach (minify_golden_outputs($srcDir) as $name => $output) {
            t_true(strlen($output) > 0, "$name is not empty");
            t_equal(
                sha1_file($goldenDir . '/' . $name),
                sha1($output),
                "$name differs from its golden file (" . strlen($output) . ' bytes instead of ' . filesize($goldenDir . '/' . $name) . ')'
            );
        }
    },

    '950 minify: no deprecation is raised' => function () use ($srcDir) {
        $messages = [];
        $previous = set_error_handler(function ($no, $str, $file, $line) use (&$messages) {
            $messages[] = $str . ' (' . basename($file) . ':' . $line . ')';
            return true;
        });
        $level = error_reporting(E_ALL);
        try {
            minify_golden_outputs($srcDir);
        } finally {
            error_reporting($level);
            restore_error_handler();
        }
        t_equal([], array_slice(array_unique($messages), 0, 5), 'deprecations or warnings (first five)');
    },

    '950 minify: Siberian\'s local patches are still in the bundled copy' => function () use ($vendor, $root) {
        t_true(method_exists(MatthiasMullie\Minify\JS::class, 'concat'), 'JS::concat(), called by Siberian\Minify');
        t_true(property_exists(MatthiasMullie\Minify\JS::class, 'concat_only'), 'JS::$concat_only');

        $minify = file_get_contents($vendor . '/Minify.php');
        t_true(strpos($minify, 'function concat(') !== false, 'Minify::concat()');
        t_true(strpos(file_get_contents($vendor . '/CSS.php'), 'implode_polyfill(') !== false, 'CSS uses implode_polyfill()');
        t_true(strpos(file_get_contents($vendor . '/JS.php'), 'file_exists($dataDir') !== false, 'JS guards its data files');

        $siberianMinify = file_get_contents($root . '/siberian/lib/Siberian/Minify.php');
        t_true(strpos($siberianMinify, '->concat(') !== false, 'Siberian\Minify still calls concat()');
    },

    '950 minify: the JS constructor does not use \'parent\' in a callable' => function () use ($vendor) {
        $source = file_get_contents($vendor . '/JS.php');
        t_true(strpos($source, "array('parent'") === false && strpos($source, "'parent::") === false, "no 'parent' callable");
        t_true(strpos($source, 'parent::__construct(...func_get_args());') !== false, 'direct call of the parent constructor');
    },
];
