<?php
/**
 * Settings > Advanced > Tools: the page lists "Siberian informations", "Restore Apps sources", then "Integrity check".
 * The integrity check runs by slices (a request no longer hashes ~40 000 files), reports modified, missing and
 * unreadable files apart, can skip the app sources, answers with a clear error when manifest.json is missing, and the
 * result is a table with a maximum height. Before, a missing file was announced as "Everything seems ok".
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$phtml = $siberian . '/app/sae/design/desktop/backoffice/template/backoffice/advanced/tools.phtml';
$js = $siberian . '/app/sae/design/desktop/backoffice/js/controllers/advanced.js';
$controller = $siberian . '/app/sae/modules/Backoffice/controllers/Advanced/ToolsController.php';
$poFile = $siberian . '/languages/base/c_backoffice.po';

if (!function_exists('p__')) {
    function p__($context, $text)
    {
        return $text;
    }
}

/**
 * An installation of $count files plus its manifest, in a fresh temp directory (with a trailing slash).
 */
$makeInstall = static function (int $count = 7) {
    $base = sys_get_temp_dir() . '/integrity-' . bin2hex(random_bytes(4)) . '/';
    mkdir($base . 'lib', 0777, true);
    mkdir($base . 'var/apps/browser', 0777, true);
    $manifest = [];
    for ($i = 0; $i < $count; $i++) {
        $file = ($i % 2 === 0 ? 'lib/f' : 'var/apps/browser/f') . $i . '.txt';
        file_put_contents($base . $file, 'content ' . $i);
        $manifest[] = ['file' => $file, 'hash' => md5('content ' . $i)];
    }
    file_put_contents($base . 'manifest.json', json_encode($manifest));
    return $base;
};

$rm = static function ($dir) {
    exec('rm -rf ' . escapeshellarg($dir));
};

return [
    'modified and missing files are reported, unreadable apart' => function () use ($makeInstall, $rm) {
        $base = $makeInstall();
        file_put_contents($base . 'lib/f0.txt', 'changed');
        unlink($base . 'var/apps/browser/f1.txt');
        $r = Siberian_Tools_Integrity::checkIntegrity($base);
        t_equal(['lib/f0.txt'], $r['hash'], 'modified');
        t_equal(['var/apps/browser/f1.txt'], $r['missing'], 'missing');
        t_equal([], $r['unreadable'], 'unreadable');
        $rm($base);
    },

    'a directory in place of a file is unreadable, not modified' => function () use ($makeInstall, $rm) {
        $base = $makeInstall();
        unlink($base . 'lib/f0.txt');
        mkdir($base . 'lib/f0.txt');
        $r = Siberian_Tools_Integrity::checkIntegrity($base);
        t_equal(['lib/f0.txt'], $r['unreadable']);
        t_equal([], $r['hash']);
        $rm($base);
    },

    'entries leaving the installation are never read' => function () use ($rm) {
        $base = sys_get_temp_dir() . '/integrity-' . bin2hex(random_bytes(4)) . '/';
        mkdir($base, 0777, true);
        file_put_contents($base . 'manifest.json', json_encode([
            ['file' => '../outside.txt', 'hash' => md5('x')],
            ['file' => '/etc/passwd', 'hash' => md5('x')],
            ['file' => 'ok/../../x', 'hash' => md5('x')],
        ]));
        $r = Siberian_Tools_Integrity::checkIntegrity($base);
        t_equal(['../outside.txt', '/etc/passwd', 'ok/../../x'], $r['unreadable']);
        t_equal([], $r['hash']);
        t_equal([], $r['missing']);
        $rm($base);
    },

    'slices add up to the full check and next walks the manifest' => function () use ($makeInstall, $rm) {
        $base = $makeInstall(7);
        file_put_contents($base . 'lib/f0.txt', 'changed');
        file_put_contents($base . 'lib/f4.txt', 'changed');
        unlink($base . 'var/apps/browser/f5.txt');

        $hash = $missing = [];
        $offset = 0;
        $nexts = [];
        do {
            $s = Siberian_Tools_Integrity::check($offset, 3, 'all', $base);
            t_equal(7, $s['total']);
            $hash = array_merge($hash, $s['hash']);
            $missing = array_merge($missing, $s['missing']);
            $nexts[] = $s['next'];
            $offset = (int) $s['next'];
        } while (!$s['done']);

        t_equal([3, 6, null], $nexts, 'next');
        t_equal(7, $s['checked'], 'checked at the end');
        $full = Siberian_Tools_Integrity::checkIntegrity($base);
        t_equal($full['hash'], $hash);
        t_equal($full['missing'], $missing);
        t_equal(['lib/f0.txt', 'lib/f4.txt'], $hash);
        $rm($base);
    },

    'core scope skips the app sources' => function () use ($makeInstall, $rm) {
        $base = $makeInstall(6);
        file_put_contents($base . 'var/apps/browser/f1.txt', 'changed');
        file_put_contents($base . 'lib/f2.txt', 'changed');

        $all = Siberian_Tools_Integrity::check(0, 100, 'all', $base);
        t_equal(6, $all['total']);
        t_equal(['var/apps/browser/f1.txt', 'lib/f2.txt'], $all['hash'], 'manifest order');

        $core = Siberian_Tools_Integrity::check(0, 100, 'core', $base);
        t_equal(3, $core['total'], 'only lib/ files');
        t_equal(['lib/f2.txt'], $core['hash']);
        $rm($base);
    },

    'limit and offset are clamped' => function () use ($makeInstall, $rm) {
        $base = $makeInstall(4);
        $s = Siberian_Tools_Integrity::check(-5, 0, 'all', $base);
        t_equal(1, $s['checked'], 'limit is at least 1');
        t_equal(1, $s['next']);
        $s = Siberian_Tools_Integrity::check(0, 999999, 'all', $base);
        t_true($s['done'], 'limit is capped but the small install is done');
        $rm($base);
    },

    'a missing or invalid manifest is a clear error, not a warning' => function () use ($rm) {
        $base = sys_get_temp_dir() . '/integrity-' . bin2hex(random_bytes(4)) . '/';
        mkdir($base, 0777, true);
        $thrown = null;
        try {
            Siberian_Tools_Integrity::check(0, 10, 'all', $base);
        } catch (Exception $e) {
            $thrown = $e->getMessage();
        }
        t_true($thrown !== null && strpos($thrown, 'manifest.json') !== false, 'missing manifest');

        file_put_contents($base . 'manifest.json', '{not json');
        $thrown = null;
        try {
            Siberian_Tools_Integrity::check(0, 10, 'all', $base);
        } catch (Exception $e) {
            $thrown = $e->getMessage();
        }
        t_true($thrown !== null && strpos($thrown, 'not valid') !== false, 'invalid manifest');
        $rm($base);
    },

    'malformed manifest entries are ignored' => function () use ($rm) {
        $base = sys_get_temp_dir() . '/integrity-' . bin2hex(random_bytes(4)) . '/';
        mkdir($base, 0777, true);
        file_put_contents($base . 'a.txt', 'a');
        file_put_contents($base . 'manifest.json', json_encode([
            ['file' => 'a.txt', 'hash' => strtoupper(md5('a'))],
            ['file' => 'b.txt'],
            'nope',
            ['file' => ['x'], 'hash' => 'y'],
        ]));
        $r = Siberian_Tools_Integrity::check(0, 10, 'all', $base);
        t_equal(1, $r['total']);
        t_equal([], $r['hash'], 'upper case hashes still match');
        $rm($base);
    },

    'the sections are ordered informations, restore, integrity' => function () use ($phtml) {
        $html = file_get_contents($phtml);
        $info = strpos($html, 'Siberian informations');
        $restore = strpos($html, 'Restore Apps sources');
        $integrity = strpos($html, '"Integrity check"');
        t_true($info !== false && $restore !== false && $integrity !== false, 'sections are there');
        t_true($info < $restore && $restore < $integrity, 'order');
    },

    'the informations section no longer submits restore_apps' => function () use ($phtml) {
        t_equal(1, substr_count(file_get_contents($phtml), 'ng-submit="restore_apps()"'));
    },

    'the result list has a maximum height and scrolls' => function () use ($phtml) {
        $html = file_get_contents($phtml);
        t_true(preg_match('/\.integrity-list\s*\{[^}]*max-height:\s*\d+px;[^}]*overflow-y:\s*auto/s', $html) === 1);
        t_true(strpos($html, 'ng-repeat="row in integrity.rows"') !== false, 'rows are a table');
    },

    'the front end checks by slices and does not announce ok on missing files' => function () use ($js, $controller) {
        $code = file_get_contents($js);
        t_true(strpos($code, 'data.done') !== false && strpos($code, 'step(data.next)') !== false, 'slices');
        t_true(strpos($code, 'data.hash.length === 0') === false, 'old ok condition is gone');
        t_true(strpos($code, 'integrity.rows.length === 0') !== false, 'ok only when nothing was found');
        $php = file_get_contents($controller);
        t_true(strpos($php, 'Siberian_Tools_Integrity::check(') !== false, 'the controller uses slices');
        t_true(strpos($php, "'error' => true") !== false, 'the controller answers errors as json');
    },

    'new texts are in the po catalog' => function () use ($poFile) {
        $po = file_get_contents($poFile);
        foreach (['Modified', 'Missing', 'Unreadable', 'App sources', 'Files checked'] as $text) {
            t_true(strpos($po, "msgctxt \"backoffice\"\nmsgid \"$text\"\n") !== false, $text);
        }
    },
];
