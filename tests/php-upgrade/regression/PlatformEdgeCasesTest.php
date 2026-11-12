<?php
/**
 * Edge cases of Siberian\Update\Platform that PlatformUpdateTest does not cover:
 * a write that does nothing, an old package of the same name, an answer with an error and a 200 status,
 * a version in the url, the timeouts.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Update/Platform.php';
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Update/Cli.php';

use Siberian\Update\Cli;
use Siberian\Update\Platform;

$make = function (array $answer, ?callable $write = null, string $edition = 'SAE', string $version = '5.1.21') {
    return new Platform($edition, $version, 'stable', $edition === 'SAE' ? null : 'K', 'h.example.com', [
        'post' => function () use ($answer): array {
            return $answer;
        },
        'write' => $write ?? function (string $path, string $content): void {
            file_put_contents($path, $content);
        },
    ]);
};

$tmp = function (): string {
    $dir = sys_get_temp_dir() . '/sib-plat-' . getmypid() . '-' . mt_rand();
    mkdir($dir, 0775, true);

    return $dir;
};

return [
    'a download whose write does nothing does not return a package of an earlier run' => function () use ($make, $tmp) {
        $dir = $tmp();
        file_put_contents($dir . '/update.zip', 'OLD PACKAGE');
        $platform = $make([200, 'NEW PACKAGE'], function (string $path, string $content): void {
            // the disk is full, or the write failed silently
        });
        $message = null;
        try {
            $path = $platform->download(['url' => 'http://x/u', 'filename' => 'update.zip'], $dir);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        t_equal('#102: Unable to fetch the update. Please, try again later.', $message, 'the old file must not be used');
    },
    'a download replaces an earlier package of the same name' => function () use ($make, $tmp) {
        $dir = $tmp();
        file_put_contents($dir . '/update.zip', 'OLD PACKAGE');
        $path = $make([200, 'NEW PACKAGE'])->download(['url' => 'http://x/u', 'filename' => 'update.zip'], $dir);
        t_equal('NEW PACKAGE', file_get_contents($path));
    },
    'a trailing slash on the directory does not double' => function () use ($make, $tmp) {
        $dir = $tmp();
        $path = $make([200, 'P'])->download(['url' => 'http://x/u', 'filename' => 'a.zip'], $dir . '/');
        t_equal($dir . '/a.zip', $path);
    },
    'the check carries the installed version in the url' => function () {
        $seen = null;
        $platform = new Platform('SAE', '5.1.21', 'beta', null, null, [
            'post' => function (string $url) use (&$seen): array {
                $seen = $url;

                return [200, '{"url":"u","filename":"f.zip"}'];
            },
            'write' => function () {
            },
        ]);
        $platform->check();
        t_equal('https://beta-updates02.siberiancms.com/check.php?version=5.1.21', $seen);
    },
    'a download uses the long timeout, the check the short one' => function () use ($tmp) {
        $timeouts = [];
        $platform = new Platform('SAE', '5.1.21', 'stable', null, null, [
            'post' => function (string $url, array $fields, int $timeout) use (&$timeouts): array {
                $timeouts[] = $timeout;

                return [200, '{"url":"http://x/u","filename":"f.zip"}'];
            },
            'write' => function (string $path, string $content): void {
                file_put_contents($path, $content);
            },
        ]);
        $available = $platform->check();
        $platform->download($available, $tmp());
        t_equal([30, 1800], $timeouts);
    },
    'an error answer with a 200 status is not reported as up to date' => function () use ($make) {
        // The update server answers {"error":true,"message":"..."} with 4xx today (see the 403 case). If it ever answers it
        // with 200, the user must not read "Your system is up to date." for a refused licence.
        $result = null;
        $message = null;
        try {
            $result = $make([200, '{"error":true,"message":"Your license is expired."}'], null, 'MAE')->check();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        t_true($message !== null || (($result['message'] ?? '') !== 'Your system is up to date.'), 'error shown as up to date');
    },
    'parseOptions: -y, repeated options and a bare confirm' => function () {
        $o = Cli::parseOptions(['-y', '--yes', '--confirm=', '--reinstall']);
        t_equal(true, $o['yes']);
        t_equal(true, $o['reinstall']);
        t_equal('', $o['confirm']);
        t_equal([], $o['unknown']);
    },
    'parseOptions: unknown options are all reported, in order' => function () {
        $o = Cli::parseOptions(['--force', '--yes', 'x']);
        t_equal(['--force', 'x'], $o['unknown']);
        t_equal(true, $o['yes']);
    },
    'the confirm phrase carries the version and nothing else' => function () {
        t_equal('yes-proceed-to-update-6.0.0', Cli::confirmPhrase('6.0.0'));
    },
    'cli() refuses an unknown option with the usage exit code' => function () {
        ob_start();
        $code = Cli::cli(['cli', 'update', '--nope']);
        $out = ob_get_clean();
        t_equal(Cli::EXIT_USAGE, $code);
        t_true(strpos($out, 'Unknown option: --nope') !== false);
        t_true(strpos($out, 'Usage:') !== false);
    },
];
