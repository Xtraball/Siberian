<?php
/**
 * The platform update of the back office and `php cli update` go through the same classes: Siberian\Update\Platform
 * (channel, licence, check, download) and Installer_Model_Installer_Update (permissions, copy, install, rebuilds).
 * The rules of the update server are tested here with fakes, and the wiring is checked in the sources so the two ways
 * of updating cannot drift apart again.
 */
$root = dirname(__DIR__, 3);
require_once $root . '/siberian/lib/Siberian/Update/Platform.php';
require_once $root . '/siberian/lib/Siberian/Update/Cli.php';

use Siberian\Update\Cli;
use Siberian\Update\Platform;

$platform = function (string $edition, ?string $key, array &$calls, array $answer, ?string $channel = 'stable', ?array &$written = null) {
    $written = $written ?? [];

    return new Platform($edition, '5.1.17', $channel, $key, 'apps.example.com', [
        'post' => function (string $url, array $fields, int $timeout) use (&$calls, $answer): array {
            $calls[] = compact('url', 'fields', 'timeout');

            return $answer;
        },
        'write' => function (string $path, string $content) use (&$written): void {
            $written[$path] = $content;
            file_put_contents($path, $content);
        },
    ]);
};

return [
    'the channel decides the server, anything else is stable' => function () {
        t_equal('https://production-updates02.siberiancms.com', Platform::server('production'));
        t_equal('https://updates02.siberiancms.com', Platform::server('stable'));
        t_equal('https://beta-updates02.siberiancms.com', Platform::server('beta'));
        t_equal('https://preview-updates02.siberiancms.com', Platform::server('preview'));
        t_equal('https://updates02.siberiancms.com', Platform::server('nightly'));
        t_equal('https://updates02.siberiancms.com', Platform::server(null));
    },

    'SAE sends sae=1, the other editions send the licence and the host' => function () use ($platform) {
        $calls = [];
        $platform('SAE', null, $calls, [200, '{"success":true}'])->check();
        t_equal(['sae' => '1'], $calls[0]['fields'], 'SAE needs no licence');

        $calls = [];
        $platform('MAE', 'KEY-123', $calls, [200, '{"success":true}'], 'beta')->check();
        t_equal(['licenseKey' => 'KEY-123', 'host' => 'apps.example.com'], $calls[0]['fields']);
        t_equal('https://beta-updates02.siberiancms.com/check.php?version=5.1.17', $calls[0]['url']);
        t_equal(30, $calls[0]['timeout']);
    },

    'a missing licence stops MAE and PE before any request' => function () use ($platform) {
        foreach (['MAE', 'PE'] as $edition) {
            foreach ([null, ''] as $key) {
                $calls = [];
                $message = null;
                try {
                    $platform($edition, $key, $calls, [200, '{}'])->check();
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }
                t_equal('There is no CMS license key set.', $message, "$edition without a key");
                t_equal(0, count($calls), 'no request was made');
            }
        }
    },

    'the errors of the update server are the ones of the back office' => function () use ($platform) {
        $cases = [
            [[200, ''], 'An error occurred while loading. Please, try again later.'],
            [[500, 'oops'], 'Unable to check for updates now. Please, try again later.'],
            [[200, 'not json'], 'Unable to check for updates now. Please, try again later.'],
            [[403, '{"error":true,"message":"Your license is expired."}'], 'Your license is expired.'],
        ];
        foreach ($cases as [$answer, $expected]) {
            $calls = [];
            $message = null;
            try {
                $platform('MAE', 'K', $calls, $answer)->check();
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
            t_equal($expected, $message);
        }
    },

    'up to date has a message and no url' => function () use ($platform) {
        $calls = [];
        $answer = $platform('SAE', null, $calls, [200, '{"success":true}'])->check();
        t_equal('Your system is up to date.', $answer['message']);
        t_true(empty($answer['url']));

        $answer = $platform('SAE', null, $calls, [200, '{"success":true,"url":"https://u/x.zip","filename":"x.zip","version":"5.2.0"}'])->check();
        t_equal('5.2.0', $answer['version']);
        t_true(!isset($answer['message']), 'an available update has no up-to-date message');
    },

    'the download posts the same fields to the url of the answer and writes the package' => function () use ($platform) {
        $dir = sys_get_temp_dir() . '/platform-update-' . getmypid();
        @mkdir($dir);
        $calls = [];
        $written = [];
        $path = $platform('MAE', 'K', $calls, [200, 'ZIPDATA'], 'stable', $written)
            ->download(['url' => 'https://u/pkg', 'filename' => 'siberian-5.2.0.zip'], $dir);

        t_equal($dir . '/siberian-5.2.0.zip', $path);
        t_equal('ZIPDATA', file_get_contents($path));
        t_equal('https://u/pkg', $calls[0]['url']);
        t_equal(['licenseKey' => 'K', 'host' => 'apps.example.com'], $calls[0]['fields']);
        t_equal(1800, $calls[0]['timeout']);
        unlink($path);
        rmdir($dir);
    },

    'the download takes a file name from the server, never a path' => function () use ($platform) {
        $dir = sys_get_temp_dir() . '/platform-update-b-' . getmypid();
        @mkdir($dir);
        $calls = [];
        $written = [];
        $path = $platform('SAE', null, $calls, [200, 'Z'], 'stable', $written)
            ->download(['url' => 'https://u/pkg', 'filename' => '../../../etc/cron.d/evil.zip'], $dir);

        t_equal($dir . '/evil.zip', $path, 'the directory part is dropped');
        t_true(!file_exists($dir . '/../../../etc/cron.d/evil.zip'));
        unlink($path);

        foreach (['..', '.'] as $name) {
            $message = null;
            try {
                $platform('SAE', null, $calls, [200, 'Z'])->download(['url' => 'https://u/pkg', 'filename' => $name], $dir);
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
            t_equal('#102: Unable to fetch the update. Please, try again later.', $message, "name $name");
        }
        rmdir($dir);
    },

    'the download errors are #100, #101 and #102' => function () use ($platform) {
        $dir = sys_get_temp_dir();
        $cases = [
            [[500, 'x'], '#101: Unable to fetch the update. Please, try again later.'],
            [[200, ''], '#100: Unable to fetch the update. Please, try again later.'],
        ];
        foreach ($cases as [$answer, $expected]) {
            $calls = [];
            $message = null;
            try {
                $platform('SAE', null, $calls, $answer)->download(['url' => 'https://u/p', 'filename' => 'a.zip'], $dir);
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
            t_equal($expected, $message);
        }

        // A writer that wrote nothing
        $calls = [];
        $silent = new Platform('SAE', '5.1.17', 'stable', null, null, [
            'post' => function () {
                return [200, 'DATA'];
            },
            'write' => function () {
            },
        ]);
        $message = null;
        try {
            $silent->download(['url' => 'https://u/p', 'filename' => 'never-written-' . getmypid() . '.zip'], $dir);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        t_equal('#102: Unable to fetch the update. Please, try again later.', $message);
    },

    'the command line options' => function () {
        $o = Cli::parseOptions(['--yes', '--reinstall', '--confirm=yes-proceed-to-update-6.0.0']);
        t_true($o['yes'] && $o['reinstall'] && !$o['check-only']);
        t_equal('yes-proceed-to-update-6.0.0', $o['confirm']);
        t_equal([], $o['unknown']);

        $o = Cli::parseOptions(['--force', '--yes']);
        t_equal(['--force'], $o['unknown'], 'an unknown option is refused, not ignored');
        t_equal(null, $o['confirm']);
    },

    'a major update asks for the phrase of the back office, --yes does not replace it' => function () {
        t_equal('yes-proceed-to-update-6.0.0', Cli::confirmPhrase('6.0.0'));

        $root = dirname(__DIR__, 3);
        $controller = file_get_contents($root . '/siberian/app/sae/modules/Installer/controllers/Backoffice/ModuleController.php');
        t_true(strpos($controller, "'confirmKey' => '" . Cli::CONFIRM_PHRASE . "'") !== false,
            'the phrase of the CLI is the one the back office shows');

        $cli = file_get_contents($root . '/siberian/lib/Siberian/Update/Cli.php');
        t_true(strpos($cli, "if (\$given !== \$phrase)") !== false, 'the phrase is compared as typed');
        t_true(strpos($cli, "\$options['yes']") !== false);
        // --yes is only read for the reinstall question, never for the major confirmation
        $major = substr($cli, strpos($cli, "is_major"), strpos($cli, 'reinstallModule($package') - strpos($cli, "is_major"));
        t_true(strpos($major, "options['yes']") === false, 'the major confirmation does not look at --yes');
    },

    'the back office and the command line share the same steps' => function () {
        $root = dirname(__DIR__, 3);
        $controller = file_get_contents($root . '/siberian/app/sae/modules/Installer/controllers/Backoffice/ModuleController.php');
        $cli = file_get_contents($root . '/siberian/lib/Siberian/Update/Cli.php');

        foreach (['::platform(', '::checkPermissions(', '::copyPackage(', '::installPending(', '::finalize('] as $step) {
            t_true(strpos($controller, 'Installer_Model_Installer_Update' . $step) !== false, "back office: $step");
        }
        foreach (['::platform(', '::checkPermissions(', '::copyPackage(', '::finalize('] as $step) {
            t_true(strpos($cli, 'Installer_Model_Installer_Update' . $step) !== false, "cli: $step");
        }
        t_true(strpos($cli, '::installPending(') !== false, 'cli: installPending');

        // The pieces were moved, not copied: the back office no longer carries its own
        t_true(strpos($controller, 'check.php') === false, 'no second copy of the check');
        t_true(strpos($controller, 'Zend_Http_Client') === false, 'no second copy of the download');
        t_true(strpos($controller, 'Module_Parser') === false, 'no second copy of the permissions and copy');
    },

    'the install runs in a new process after the copy, and update:install can resume it' => function () {
        $root = dirname(__DIR__, 3);
        $cli = file_get_contents($root . '/siberian/lib/Siberian/Update/Cli.php');
        $script = file_get_contents($root . '/siberian/cli');

        $run = substr($cli, strpos($cli, 'copyPackage($file)'), 700);
        t_true(strpos($run, 'installInNewProcess') !== false, 'the install is not run by the process that holds the old code');
        t_true(strpos($script, "case 'update:install':") !== false);
        t_true(strpos($script, "case 'update':") !== false && strpos($script, "case 'update:check':") !== false);
        t_true(strpos($script, 'Update\\Cli::install($argv)') !== false);
    },
];
