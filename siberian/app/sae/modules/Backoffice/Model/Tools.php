<?php

namespace Backoffice\Model;

use Siberian\Cron;
use Siberian\Exception;
use Siberian\Provider;
use Siberian\Request;
use Siberian\Version;
use Siberian\Cache;
use Siberian\Cache\Design as CacheDesign;
use Siberian\Autoupdater;

/**
 * Class Tools
 * @package Backoffice\Model
 */
class Tools
{
    const RESTORE_APP_SOURCES = 'backoffice_cron.restore_app_sources';
    const REBUILD_MANIFEST = 'backoffice_cron.rebuild_manifest';

    /**
     * @param Cron|null $cron
     * @return string
     * @throws \Zend_Exception
     */
    public static function rebuildManifest (Cron $cron = null): string
    {
        try {
            if ($cron !== null) {
                $cron->log(__('Rebuilding application manifest files.'));
            }

            Cache::__clearCache();
            unlink(path('/var/cache/design.cache'));

            $defaultCache = \Zend_Registry::get("cache");
            $defaultCache->clean(\Zend_Cache::CLEANING_MODE_ALL);

            Autoupdater::configure();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return p__('backoffice', '[Rebuild manifest] Manifest rebuild with succes!');
    }

    /**
     * @param $name
     */
    public static function scheduleTask ($name)
    {
        // Not scheduling any new task if RESTORE_APP_SOURCES is already due!
        $restoreAppSources = filter_var(__get(self::RESTORE_APP_SOURCES), FILTER_VALIDATE_BOOLEAN);
        if ($restoreAppSources === true) {
            return;
        }

        switch ($name) {
            case self::RESTORE_APP_SOURCES:
                __set(self::RESTORE_APP_SOURCES, 1);
                break;
            case self::REBUILD_MANIFEST:
                __set(self::REBUILD_MANIFEST, 1);
                break;
        }
    }

    /**
     * @param Cron|null $cron
     * @return array|string
     * @throws \Zend_Exception
     */
    public static function watch (Cron $cron = null)
    {
        try {
            if ($cron !== null) {
                $cron->log(p__('backoffice', '[Backoffice::Tools] Looking for actions.'));
            }

            $restoreAppSources = filter_var(__get(self::RESTORE_APP_SOURCES), FILTER_VALIDATE_BOOLEAN);
            if ($restoreAppSources === true) {
                // In that event, we set the manifest to 0 to prevent acting twice.
                __set(self::RESTORE_APP_SOURCES, 0);
                __set(self::REBUILD_MANIFEST, 0);
                return self::restoreapps($cron);
            }

            $rebuildManifest = filter_var(__get(self::REBUILD_MANIFEST), FILTER_VALIDATE_BOOLEAN);
            if ($rebuildManifest === true) {
                __set(self::REBUILD_MANIFEST, 0);
                return self::rebuildManifest($cron);
            }
        } catch (\Exception $e) {
            if ($cron !== null) {
                $cron->log(p__('backoffice', '[Backoffice::Tools] error %s.', $e->getMessage()));
            }
        }
        return 'noop';
    }

    /**
     * @param null $cron
     * @return array
     */
    public static function restoreapps ($cron = null): array
    {
        $oldUmask = umask(0);
        try {
            $wgetBin = 'wget';
            $isDarwin = exec('uname');
            # MacOSX
            if (strpos($isDarwin, 'arwin') !== false) {
                $wgetBin = '/usr/local/bin/wget';
            }

            $varApps = path('var/apps');
            self::checkWriteable();

            if (!is_writable($varApps)) {
                throw new Exception(
                    p__('backoffice',
                        'The folder %s is not writable, please check that your web user can write to it.',
                        $varApps));
            }

            $version = Version::VERSION;
            $versionKey = '%VERSION%';

            // Check if release exists
            $sources = Provider::getSources();

            $releaseUrl = str_replace($versionKey, $version, $sources['release_url']['url']);
            Request::get($releaseUrl);
            if (Request::$statusCode == '404') {
                throw new Exception(__('There is not corresponding release to restore from, process aborted!'));
            }

            $browser = str_replace($versionKey, $version, $sources['browser']['url']);
            $android = str_replace($versionKey, $version, $sources['android']['url']);
            $ios = str_replace($versionKey, $version, $sources['ios']['url']);
            $iosNoads = str_replace($versionKey, $version, $sources['ios_noads']['url']);

            // Clean-up before run!
            chdir($varApps . '/ionic');
            self::verboseExec('rm -fv ./android.tgz');
            self::verboseExec('rm -fv ./ios.tgz');
            self::verboseExec('rm -fv ./ios-noads.tgz');
            self::verboseExec('rm -fv ../browser.tgz');

            // Download archives from GitHub
            chdir($varApps);
            self::verboseExec($wgetBin . ' --no-check-certificate -v ' . $browser);
            chdir($varApps . '/ionic');
            self::verboseExec($wgetBin . ' --no-check-certificate -v ' . $android);
            self::verboseExec($wgetBin . ' --no-check-certificate -v ' . $ios);
            self::verboseExec($wgetBin . ' --no-check-certificate -v ' . $iosNoads);

            if (!is_readable('./android.tgz') ||
                !is_readable('./ios.tgz') ||
                !is_readable('./ios-noads.tgz') ||
                !is_readable('../browser.tgz')) {
                throw new Exception(__('Something went wrong while restoring files, process aborted!'));
            }

            // Clean-up & Extract!
            chdir($varApps);
            self::verboseExec('rm -Rfv ./browser');
            self::verboseExec('rm -Rfv ./overview');
            self::verboseExec('tar pvxzf browser.tgz');
            self::verboseExec('mv ./browser ./overview'); // use mv, instead of cp, and untar browser a second time
            self::verboseExec('tar pvxzf browser.tgz');
            chdir($varApps . '/ionic');
            self::verboseExec('rm -Rfv ./android');
            self::verboseExec('tar pvxzf android.tgz');
            self::verboseExec('rm -Rfv ./ios');
            self::verboseExec('tar pvxzf ios.tgz');
            self::verboseExec('rm -Rfv ./ios-noads');
            self::verboseExec('tar pxzf ios-noads.tgz');

            // Clean-up after work!
            chdir($varApps . '/ionic');
            self::verboseExec('rm -fv ./android.tgz');
            self::verboseExec('rm -fv ./ios.tgz');
            self::verboseExec('rm -fv ./ios-noads.tgz');
            self::verboseExec('rm -fv ../browser.tgz');

            self::checkWriteable();

            // On success, we must then rebuild the manifest!
            $message = self::rebuildManifest($cron);

            $messageSources = p__('backoffice', '[Restore sources] Sources are successfully restored.');

            $payload = [
                'success' => true,
                'message' => $messageSources . '<br />' . $message
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        umask($oldUmask);

        return $payload;
    }

    /**
     * @throws \Zend_Exception
     */
    public static function checkWriteable()
    {
        // Ensure all folders var/apps are writable, parent MUST be writeable in order to remove file in it.
        $varApps = path('var/apps');
        self::verboseExec('chmod -Rv 777 "' . $varApps . '"');
    }

    /**
     * @param $command
     * @throws \Zend_Exception
     */
    public static function verboseExec($command)
    {
        $output = [];
        exec($command, $output);

        // Final loggind
        $logger = \Zend_Registry::get('logger');
        $logger->info(print_r($output, true) . PHP_EOL);
    }
}