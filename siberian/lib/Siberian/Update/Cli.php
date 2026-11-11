<?php

namespace Siberian\Update;

/**
 * `php cli update:check` and `php cli update`: the platform update of the back office, from the command line.
 *
 * Every step is the one of the back office, through the same classes (Siberian\Update\Platform for the licence, the
 * channel and the download; Installer_Model_Installer_Update for permissions, copy, install and the final rebuilds), in
 * the same order:
 *
 *   check -> download -> read the package -> (major: typed phrase) -> (same version: reinstall?) -> permissions
 *         -> copy -> install -> finalize -> restore the apps or rebuild the manifest
 *
 * Options of `update`:
 *   --yes                          do not ask the questions that have a safe default (never skips a major update)
 *   --confirm=<phrase>             the phrase of a major update, `yes-proceed-to-update-<version>`; typed when interactive
 *   --reinstall                    reinstall when the same version is already installed (opt-in, as in the back office)
 *   --check-only                   same as update:check
 *
 * Exit codes: 0 done or up to date, 1 failed, 2 wrong usage, 3 refused (major update not confirmed), 4 already running.
 */
class Cli
{
    public const EXIT_OK = 0;
    public const EXIT_FAILED = 1;
    public const EXIT_USAGE = 2;
    public const EXIT_REFUSED = 3;
    public const EXIT_RUNNING = 4;

    public const CONFIRM_PHRASE = 'yes-proceed-to-update-#VERSION#';

    /**
     * @param array $argv the arguments of `php cli`: [script, action, ...options]
     * @return int the exit code
     */
    public static function cli(array $argv): int
    {
        if (PHP_SAPI !== 'cli') {
            echo 'This command runs from the command line only.' . PHP_EOL;

            return self::EXIT_USAGE;
        }

        $options = self::parseOptions(array_slice($argv, 2));
        if ($options['unknown'] !== []) {
            echo 'Unknown option: ' . implode(', ', $options['unknown']) . PHP_EOL;
            echo 'Usage: php cli update:check | php cli update [--yes] [--confirm=<phrase>] [--reinstall]' . PHP_EOL;

            return self::EXIT_USAGE;
        }

        $checkOnly = ($argv[1] ?? '') === 'update:check' || $options['check-only'];

        try {
            $host = (string) \System_Model_Config::getValueFor('main_domain');
            $platform = \Installer_Model_Installer_Update::platform($host !== '' ? $host : null);

            echo sprintf(
                'Installed: Siberian %s %s, channel "%s".',
                \Siberian_Version::TYPE,
                \Siberian_Version::VERSION,
                (string) (__get('update_channel') ?: 'stable')) . PHP_EOL;

            $available = $platform->check();

            if (empty($available['url'])) {
                echo ($available['message'] ?? 'Your system is up to date.') . PHP_EOL;

                return self::EXIT_OK;
            }

            echo sprintf('Available: %s.', $available['version'] ?? $available['filename'] ?? 'a new version') . PHP_EOL;

            if ($checkOnly) {
                echo 'Run `php cli update` to install it.' . PHP_EOL;

                return self::EXIT_OK;
            }

            return self::run($platform, $available, $options);
        } catch (\Throwable $e) {
            echo 'Update failed: ' . $e->getMessage() . PHP_EOL;

            return self::EXIT_FAILED;
        }
    }

    /**
     * @param array $arguments the options after the action
     * @return array yes, reinstall, check-only, confirm and unknown (the options that are not known)
     */
    public static function parseOptions(array $arguments): array
    {
        $options = ['yes' => false, 'reinstall' => false, 'check-only' => false, 'confirm' => null, 'unknown' => []];

        foreach ($arguments as $argument) {
            $argument = (string) $argument;
            if ($argument === '--yes' || $argument === '-y') {
                $options['yes'] = true;
            } else if ($argument === '--reinstall') {
                $options['reinstall'] = true;
            } else if ($argument === '--check-only') {
                $options['check-only'] = true;
            } else if (strpos($argument, '--confirm=') === 0) {
                $options['confirm'] = substr($argument, strlen('--confirm='));
            } else {
                $options['unknown'][] = $argument;
            }
        }

        return $options;
    }

    /**
     * The phrase a major update asks for, as the back office shows it.
     *
     * @param string $version
     * @return string
     */
    public static function confirmPhrase(string $version): string
    {
        return str_replace('#VERSION#', $version, self::CONFIRM_PHRASE);
    }

    /**
     * @param Platform $platform
     * @param array $available
     * @param array $options
     * @return int
     * @throws \Exception
     */
    private static function run(Platform $platform, array $available, array $options): int
    {
        $lock = self::lock();
        if ($lock === null) {
            echo 'Another update is running (var/tmp/cli-update.lock), nothing was done.' . PHP_EOL;

            return self::EXIT_RUNNING;
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            echo 'Downloading ...' . PHP_EOL;
            $file = $platform->download($available, \Core_Model_Directory::getTmpDirectory(true));

            // The same reading of the package as the back office
            $installer = new \Installer_Model_Installer();
            $installer->parse($file);
            $package = $installer->getPackageDetails();
            $version = (string) $package->getVersion();
            $releaseNote = $package->getReleaseNote() ?? [];

            echo sprintf('Downloaded %s %s.', $package->getName(), $version) . PHP_EOL;
            if (!empty($releaseNote['url'])) {
                echo 'Release note: ' . $releaseNote['url'] . PHP_EOL;
            }

            if (!empty($releaseNote['is_major'])) {
                $phrase = self::confirmPhrase($version);
                echo sprintf('Version %s introduces breaking changes, read the release note first.', $version) . PHP_EOL;

                $given = $options['confirm'];
                if ($given === null && self::isInteractive()) {
                    $given = trim((string) readline(sprintf('Type "%s" to continue: ', $phrase)));
                }

                if ($given !== $phrase) {
                    echo sprintf('Not confirmed. Run again with --confirm=%s to proceed.', $phrase) . PHP_EOL;

                    return self::EXIT_REFUSED;
                }
            }

            $reinstall = self::reinstallModule($package, $options);

            echo 'Checking permissions ...' . PHP_EOL;
            \Installer_Model_Installer_Update::checkPermissions($file);

            echo 'Copying files ...' . PHP_EOL;
            \Installer_Model_Installer_Update::copyPackage($file);

            // The copy replaced the PHP files this process has already loaded: like the back office (where the install
            // is another request), the install runs in a new process, with the new code only.
            echo 'Installing ...' . PHP_EOL;
            $code = self::installInNewProcess($reinstall, (bool) $package->getRestoreApps());
            if ($code !== 0) {
                throw new \Exception(sprintf(
                    'The installation stopped (exit code %d). The files are copied: run `php cli update:install` again to resume.',
                    $code));
            }

            echo sprintf('Siberian updated to %s.', $version) . PHP_EOL;

            return self::EXIT_OK;
        } finally {
            self::unlock($lock);
        }
    }

    /**
     * The step after the copy, the hidden action `php cli update:install`: install what the copied files brought,
     * rebuild, then ask for the apps to be restored or the manifest rebuilt. It can be run again by hand to resume.
     *
     * @param array $argv
     * @return int
     */
    public static function install(array $argv): int
    {
        $reinstall = '';
        $restoreApps = false;
        foreach (array_slice($argv, 2) as $argument) {
            if (strpos((string) $argument, '--reinstall=') === 0) {
                $reinstall = substr((string) $argument, strlen('--reinstall='));
            } else if ($argument === '--restore-apps') {
                $restoreApps = true;
            }
        }

        try {
            set_time_limit(0);
            $modules = \Installer_Model_Installer_Update::installPending($reinstall);
            echo sprintf('Installed %d module(s).', count($modules)) . PHP_EOL;

            \Installer_Model_Installer_Update::finalize(self::baseUrl());

            // What the back office does at the end of an update
            if ($restoreApps) {
                \Backoffice\Model\Tools::scheduleTask(\Backoffice\Model\Tools::RESTORE_APP_SOURCES);
                echo 'The apps sources will be restored by the next cron run.' . PHP_EOL;
            } else {
                \Backoffice\Model\Tools::scheduleTask(\Backoffice\Model\Tools::REBUILD_MANIFEST);
                echo 'The apps manifest will be rebuilt by the next cron run.' . PHP_EOL;
            }

            return self::EXIT_OK;
        } catch (\Throwable $e) {
            echo 'Install failed: ' . $e->getMessage() . PHP_EOL;

            return self::EXIT_FAILED;
        }
    }

    /**
     * @return string protocol and host of the installation, empty when no domain is configured
     */
    public static function baseUrl(): string
    {
        $domain = trim((string) \System_Model_Config::getValueFor('main_domain'));
        if ($domain === '') {
            return '';
        }

        $https = filter_var(\System_Model_Config::getValueFor('use_https'), FILTER_VALIDATE_BOOLEAN);

        return ($https ? 'https://' : 'http://') . $domain;
    }

    /**
     * @param string $reinstall
     * @param bool $restoreApps
     * @return int the exit code of the install process
     */
    private static function installInNewProcess(string $reinstall, bool $restoreApps): int
    {
        $command = [PHP_BINARY, $_SERVER['SCRIPT_FILENAME'] ?? 'cli', 'update:install'];
        if ($reinstall !== '') {
            $command[] = '--reinstall=' . $reinstall;
        }
        if ($restoreApps) {
            $command[] = '--restore-apps';
        }

        $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes, getcwd());
        if (!is_resource($process)) {
            return self::EXIT_FAILED;
        }

        return proc_close($process);
    }

    /**
     * @param mixed $package
     * @param array $options
     * @return string the module to reinstall, empty for none
     */
    private static function reinstallModule($package, array $options): string
    {
        try {
            $installed = (new \Installer_Model_Installer_Module())->prepare($package->getName(), false);
            if (!$installed->isInstalled() ||
                !\Installer_Model_Installer_Reinstall::isAllowed(
                    true,
                    $installed->getVersion(),
                    $package->getVersion())) {
                return '';
            }
        } catch (\Throwable $e) {
            return '';
        }

        // Same version already installed: reinstalling is opt-in, as in the back office
        if ($options['reinstall']) {
            return (string) $package->getName();
        }

        if (!$options['yes'] && self::isInteractive()) {
            $answer = strtolower(trim((string) readline('This version is already installed. Reinstall it? [y/N] ')));

            return in_array($answer, ['y', 'yes'], true) ? (string) $package->getName() : '';
        }

        return '';
    }

    /**
     * @return bool
     */
    private static function isInteractive(): bool
    {
        return function_exists('posix_isatty') && posix_isatty(STDIN) && function_exists('readline');
    }

    /**
     * @return resource|null the lock, null when another update holds it
     */
    private static function lock()
    {
        $path = \Core_Model_Directory::getBasePathTo('/var/tmp/cli-update.lock');
        $handle = fopen($path, 'c');
        if ($handle === false || !flock($handle, LOCK_EX | LOCK_NB)) {
            return null;
        }

        return $handle;
    }

    /**
     * @param resource $lock
     */
    private static function unlock($lock): void
    {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
