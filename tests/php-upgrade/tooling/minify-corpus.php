<?php
/**
 * Minify every JS and CSS file of a checkout with a given matthiasmullie/minify, one line per file:
 *   <kind> <path> <sha1 of the minified output> <bytes> <deprecations/warnings count>
 * plus one line per directory bundle (files added together, as Assets::compileFeature does).
 *
 *   php minify-corpus.php <minify dir (has src/)> <path-converter dir (has src/)> <repo root> <file list> <out>
 */
[, $minifyDir, $converterDir, $root, $listFile, $out] = $argv;

// the vendored copy calls this global of siberian/lib/System/polyfills.php
if (!function_exists('implode_polyfill')) {
    function implode_polyfill($firstArg, $secondArg = null): string
    {
        if (is_array($firstArg)) {
            return implode($secondArg ?? '', $firstArg);
        }
        return implode($firstArg, $secondArg);
    }
}

foreach (['ConverterInterface', 'Converter', 'NoConverter'] as $c) {
    $f = $converterDir . '/src/' . $c . '.php';
    if (is_file($f)) {
        require_once $f;
    }
}
require_once $minifyDir . '/src/Exception.php';
foreach (glob($minifyDir . '/src/Exceptions/*.php') as $f) {
    require_once $f;
}
require_once $minifyDir . '/src/Minify.php';
require_once $minifyDir . '/src/CSS.php';
require_once $minifyDir . '/src/JS.php';

$messages = [];
set_error_handler(function ($no, $str, $file, $line) use (&$messages) {
    $messages[] = $str . ' @' . basename($file) . ':' . $line;
    return true;
});
error_reporting(E_ALL);

$files = array_filter(array_map('trim', file($listFile)));
$seen = [];
$handle = fopen($out, 'w');
$bundles = [];

foreach ($files as $rel) {
    $path = $root . '/' . $rel;
    if (!is_file($path)) {
        continue;
    }
    $ext = pathinfo($rel, PATHINFO_EXTENSION);
    $content = file_get_contents($path);
    $key = $ext . sha1($content);
    $bundles[dirname($rel)][$ext][] = $rel;
    if (isset($seen[$key])) {
        continue; // same content already measured, the line below is enough for the dedupe
    }
    $seen[$key] = true;
    $messages = [];
    try {
        $m = $ext === 'js' ? new MatthiasMullie\Minify\JS($path) : new MatthiasMullie\Minify\CSS($path);
        $result = $m->minify();
        fwrite($handle, implode("\t", [$ext, $rel, sha1($result), strlen($result), count($messages)]) . "\n");
    } catch (Throwable $e) {
        fwrite($handle, implode("\t", [$ext, $rel, 'EXCEPTION ' . get_class($e) . ' ' . preg_replace('/\s+/', ' ', substr($e->getMessage(), 0, 120)), 0, count($messages)]) . "\n");
    }
}

ksort($bundles);
foreach ($bundles as $dir => $byExt) {
    foreach ($byExt as $ext => $list) {
        if (count($list) < 2) {
            continue;
        }
        $messages = [];
        try {
            $m = $ext === 'js' ? new MatthiasMullie\Minify\JS() : new MatthiasMullie\Minify\CSS();
            foreach ($list as $rel) {
                $m->add($root . '/' . $rel);
            }
            $result = $m->minify();
            fwrite($handle, implode("\t", ['bundle-' . $ext, $dir . ' (' . count($list) . ')', sha1($result), strlen($result), count($messages)]) . "\n");
        } catch (Throwable $e) {
            fwrite($handle, implode("\t", ['bundle-' . $ext, $dir, 'EXCEPTION ' . get_class($e), 0, count($messages)]) . "\n");
        }
    }
}
fclose($handle);
