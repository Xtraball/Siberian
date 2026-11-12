<?php
/**
 * downloadAction served any file when the path began with var/tmp or var/apps, "../" included, and echoed an unknown
 * path back in a content type the caller chose. SafePath decides on the real path.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Input.php';
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/SafePath.php';

use Siberian\Input;
use Siberian\SafePath;

$safePathFixture = function () {
    $base = sys_get_temp_dir() . '/safepath-' . getmypid() . '-' . mt_rand();
    mkdir($base . '/var/tmp/sub', 0777, true);
    mkdir($base . '/var/apps', 0777, true);
    mkdir($base . '/app/configs', 0777, true);
    mkdir($base . '/var/tmpevil', 0777, true);
    file_put_contents($base . '/var/tmp/sub/export.zip', 'zip');
    file_put_contents($base . '/var/apps/app.apk', 'apk');
    file_put_contents($base . '/var/apps/app.aab', 'aab');
    file_put_contents($base . '/var/tmp/sub/app.ini', 'secret, but under an allowed root');
    file_put_contents($base . '/app/configs/app.ini', 'secret');
    file_put_contents($base . '/var/tmpevil/x.txt', 'sibling');
    @symlink($base . '/app/configs/app.ini', $base . '/var/tmp/link.ini');

    return $base;
};

return [
    'a file below an allowed root is served, by its real path' => function () use ($safePathFixture) {
        $base = $safePathFixture();
        $roots = [$base . '/var/tmp', $base . '/var/apps'];
        t_equal(realpath($base . '/var/tmp/sub/export.zip'), SafePath::file($base . '/var/tmp/sub/export.zip', $roots));
        t_equal(realpath($base . '/var/apps/app.apk'), SafePath::file($base . '/var/apps/app.apk', $roots));
    },
    'a dot-dot path that starts with the root is refused' => function () use ($safePathFixture) {
        $base = $safePathFixture();
        $roots = [$base . '/var/tmp', $base . '/var/apps'];
        t_true(strpos($base . '/var/tmp/../../app/configs/app.ini', $roots[0]) === 0, 'it passes the old prefix check');
        t_equal(null, SafePath::file($base . '/var/tmp/../../app/configs/app.ini', $roots));
        t_equal(null, SafePath::file($base . '/var/tmp/sub/../../../app/configs/app.ini', $roots));
    },
    'a sibling directory that shares the prefix, and a symbolic link out, are refused' => function () use ($safePathFixture) {
        $base = $safePathFixture();
        $roots = [$base . '/var/tmp', $base . '/var/apps'];
        t_equal(null, SafePath::file($base . '/var/tmpevil/x.txt', $roots));
        if (is_link($base . '/var/tmp/link.ini')) {
            t_equal(null, SafePath::file($base . '/var/tmp/link.ini', $roots));
        }
    },
    'a missing file, a directory, text, an array and a null byte give null' => function () use ($safePathFixture) {
        $base = $safePathFixture();
        $roots = [$base . '/var/tmp'];
        foreach ([$base . '/var/tmp/missing.zip', $base . '/var/tmp/sub', '<script>alert(1)</script>', '', null, false, [], $base . "/var/tmp/sub/export.zip\0.png"] as $i => $path) {
            t_equal(null, SafePath::file($path, $roots), "case $i");
        }
        t_equal(null, SafePath::file($base . '/var/tmp/sub/export.zip', [$base . '/no/such/root']), 'a missing root allows nothing');
        t_equal(null, SafePath::file($base . '/var/tmp/sub/export.zip', []), 'no root allows nothing');
    },
    'an extension allowlist keeps zip, apk and aab, and still refuses whatever else is under the allowed roots' => function () use ($safePathFixture) {
        $base = $safePathFixture();
        $roots = [$base . '/var/tmp', $base . '/var/apps'];
        $ext = ['zip', 'apk', 'aab'];
        t_equal(realpath($base . '/var/tmp/sub/export.zip'), SafePath::file($base . '/var/tmp/sub/export.zip', $roots, $ext));
        t_equal(realpath($base . '/var/apps/app.apk'), SafePath::file($base . '/var/apps/app.apk', $roots, $ext));
        t_equal(realpath($base . '/var/apps/app.aab'), SafePath::file($base . '/var/apps/app.aab', $roots, $ext));
        t_equal(null, SafePath::file($base . '/var/tmp/sub/app.ini', $roots, $ext), 'an .ini under an allowed root is still refused');
        t_equal(null, SafePath::file($base . '/var/apps/app.apk', $roots, ['zip']), 'a narrower list refuses apk');
        t_equal(null, SafePath::file($base . '/var/tmp/sub/export.zip', $roots, []), 'an empty list allows nothing');
        t_equal(realpath($base . '/var/tmp/sub/app.ini'), SafePath::file($base . '/var/tmp/sub/app.ini', $roots, null), 'null keeps the old no-extension-check behaviour');
    },
    'the download name has no directory, quote or line break' => function () {
        t_equal('export.zip', SafePath::downloadName('export.zip'));
        t_equal('passwd', SafePath::downloadName('../../etc/passwd'));
        t_equal('aSet-Cookie: x=1.zip', SafePath::downloadName("a\r\nSet-Cookie: x=1\".zip"));
        t_equal('fallback', SafePath::downloadName('', 'fallback'));
        t_equal('fallback', SafePath::downloadName('..', 'fallback'));
        t_equal('fallback', SafePath::downloadName(false, 'fallback'));
        t_equal('fallback', SafePath::downloadName([], 'fallback'));
        t_true(strpbrk(SafePath::downloadName("x\"\r\n;y.zip"), "\"\r\n;") === false);
    },
    'slug keeps an identifier and refuses a path, a dot, an array' => function () {
        t_equal('Folder', Input::slug('Folder'));
        t_equal('my_module-2', Input::slug(' my_module-2 '));
        foreach (['../../etc/passwd', 'a/b', 'a.b', 'a b', '', "a\nb", [], null, true, 'é'] as $i => $value) {
            t_equal('', Input::slug($value), "case $i");
            t_equal('none', Input::slug($value, 'none'), "case $i with a default");
        }
    },
    'the controllers wire the helpers in' => function () {
        $root = dirname(__DIR__, 3) . '/siberian/app/sae/modules';
        $core = file_get_contents($root . '/Core/Controller/Default/Abstract.php');
        preg_match('/public function downloadAction\(\)\s*\{(.*?)\n    \}/s', $core, $m);
        t_true(isset($m[1]) && strpos($m[1], 'SafePath::file(') !== false, 'downloadAction resolves the path');
        t_true(strpos($m[1], 'content_type') === false, 'the content type is not the caller\'s');
        t_true(strpos($m[1], "['zip', 'apk', 'aab']") !== false, 'the extension is limited to what Jenkins and the apk generator hand off');
        $features = file_get_contents($root . '/Application/controllers/Customization/FeaturesController.php');
        t_true(strpos($features, 'Input::slug($this->getRequest()->getParam(\'type\'))') !== false, 'the edit type is an identifier');
        foreach (['ModuleController.php', 'Installation/DatabaseController.php', 'Installation/ModuleController.php'] as $file) {
            $source = file_get_contents($root . '/Installer/controllers/' . $file);
            t_true(strpos($source, 'Input::slug($this->getRequest()->getParam(\'name\'))') !== false, $file);
        }
    },
];
