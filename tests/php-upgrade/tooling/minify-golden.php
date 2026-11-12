<?php
/**
 * Golden outputs of the bundled matthiasmullie/minify (#950).
 *
 *   php tests/php-upgrade/tooling/minify-golden.php            verify: exit 1 if an output changed
 *   php tests/php-upgrade/tooling/minify-golden.php --write    rewrite the golden files
 *
 * The sources are real files of the mobile app (and two hand written edge case files) in
 * tests/php-upgrade/fixtures/minify/src. The golden files were produced by the library as it was before PHP 8.2+
 * deprecations were fixed, so a later change to the library is only accepted when its output stays byte for byte the
 * same. Rewrite the golden files on purpose only, and say why in the commit: every generated app bundle changes with them.
 *
 * Siberian carries local patches in the bundled copy (`concat()` used by Siberian\Minify, `implode_polyfill()`, guards for
 * the data files). Replacing the library by a stock release loses them and changes the output (368 of 2459 outputs of the
 * whole repository differ on 1.3.75), see the test for #950.
 */

/**
 * Every output the bundled minifier is asked for, by golden file name.
 *
 * @return array<string, string>
 */
function minify_golden_outputs(string $srcDir): array
{
    $outputs = [];
    $js = [];
    $css = [];
    $files = glob($srcDir . '/*');
    sort($files);
    foreach ($files as $file) {
        $name = basename($file);
        if (substr($name, -3) === '.js') {
            $outputs[$name] = (new MatthiasMullie\Minify\JS($file))->minify();
            $js[] = $file;
        } elseif (substr($name, -4) === '.css') {
            $outputs[$name] = (new MatthiasMullie\Minify\CSS($file))->minify();
            $css[] = $file;
        }
    }

    // several files added together, as Assets::compileFeature does
    $bundleJs = new MatthiasMullie\Minify\JS();
    $concatJs = new MatthiasMullie\Minify\JS();
    foreach ($js as $file) {
        $bundleJs->add($file);
        $concatJs->add($file);
    }
    $bundleCss = new MatthiasMullie\Minify\CSS();
    foreach ($css as $file) {
        $bundleCss->add($file);
    }
    $outputs['bundle.js'] = $bundleJs->minify();
    $outputs['bundle.concat.js'] = $concatJs->concat(); // Siberian\Minify uses concat() for files that are already minified
    $outputs['bundle.css'] = $bundleCss->minify();

    return $outputs;
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    require_once dirname(__DIR__) . '/bootstrap.php';
    $srcDir = dirname(__DIR__) . '/fixtures/minify/src';
    $goldenDir = dirname(__DIR__) . '/fixtures/minify/golden';
    $write = in_array('--write', $argv, true);
    error_reporting(E_ALL & ~E_DEPRECATED); // the library may still raise them while it is being fixed
    $changed = 0;
    foreach (minify_golden_outputs($srcDir) as $name => $output) {
        $golden = $goldenDir . '/' . $name;
        if ($write) {
            file_put_contents($golden, $output);
        } elseif (!is_file($golden) || file_get_contents($golden) !== $output) {
            echo "differs: $name\n";
            $changed++;
        }
    }
    fwrite(STDERR, $write ? "golden files written\n" : "$changed output(s) differ\n");
    exit($changed ? 1 : 0);
}
