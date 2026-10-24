<?php

use Siberian\ModuleUpdates;
use Siberian\ModuleUpdates\ConfigStore;
use Siberian\ModuleUpdates\CurlTransport;

/**
 * Class Installer_Model_ModuleUpdates
 *
 * Glue between the application (settings, installed modules, licence keys, tmp directory) and the
 * framework-free Siberian\ModuleUpdates service.
 */
class Installer_Model_ModuleUpdates
{
    /**
     * Free space required in the tmp directory before downloading, in bytes.
     */
    const MIN_FREE_SPACE = 209715200;

    /**
     * @return ModuleUpdates
     */
    public static function service()
    {
        return new ModuleUpdates(
            new CurlTransport(),
            new ConfigStore(),
            Core_Model_Directory::getBasePathTo('var/cache/module-updates.json'),
            \Siberian\Version::VERSION,
            self::pinnedKeys(),
            null,
            // Only the configuration file can point somewhere else (staging, tests), never the database.
            __getConfig('module_updates_base_url') ?: ModuleUpdates::BASE_URL
        );
    }

    /**
     * The compiled-in keys, plus any set in the settings (module_updates_public_keys, JSON of key id => base64),
     * which lets a key rotation ship without a release.
     *
     * @return array
     */
    public static function pinnedKeys()
    {
        $keys = ModuleUpdates::PINNED_KEYS;
        $extra = json_decode((string) __get('module_updates_public_keys'), true);
        if (is_array($extra)) {
            foreach ($extra as $id => $key) {
                if (is_string($id) && is_string($key)) {
                    $keys[$id] = $key;
                }
            }
        }

        return $keys;
    }

    /**
     * Installed modules that carry a licence key, in the shape ModuleUpdates::pending() expects.
     *
     * @return array
     */
    public static function installed()
    {
        $installed = [];
        foreach ((new Installer_Model_Installer_Module())->findAll() as $module) {
            $code = $module->getCode();
            if (empty($code)) {
                continue;
            }
            $installed[] = [
                'code' => $code,
                'name' => $module->getName(),
                'version' => $module->getVersion(),
                'has_license' => trim((string) __get($code . '_key')) !== '',
            ];
        }

        return $installed;
    }

    /**
     * Downloads and verifies the update of one module into the tmp directory.
     *
     * @param string $code
     * @return string Path of the verified package
     * @throws ModuleUpdates\Exception
     */
    public static function download($code)
    {
        $service = self::service();
        $list = $service->refresh();

        if (!$service->termsAccepted($list)) {
            // The cached list may predate a terms change that was just accepted: look again once.
            $list = $service->refresh(true);
        }

        if (!$service->termsAccepted($list)) {
            throw new ModuleUpdates\Exception('terms_required', p__('backoffice', 'Please accept the terms of download first.'), [
                'terms' => $service->fetchTerms(),
            ]);
        }

        $pending = null;
        $installedVersion = null;
        foreach ($service->pending(self::installed(), $list) as $update) {
            if ($update['code'] === $code) {
                $pending = $update;
                $installedVersion = $update['installed'];
            }
        }
        if ($pending === null) {
            throw new ModuleUpdates\Exception('not_found', p__('backoffice', 'There is no update for this module.'));
        }
        if (!$pending['compatible']) {
            throw new ModuleUpdates\Exception('siberian_too_old', sprintf(
                p__('backoffice', 'This update requires Siberian %s or newer.'),
                $pending['min_siberian']
            ));
        }

        $siberianKey = trim((string) __get('siberiancms_key'));
        $moduleKey = trim((string) __get($code . '_key'));
        if ($siberianKey === '' || $moduleKey === '') {
            throw new ModuleUpdates\Exception('license_missing', p__('backoffice', 'Both the Siberian and the module licence keys are required.'));
        }

        $tmp = Core_Model_Directory::getTmpDirectory(true);
        if (disk_free_space($tmp) < self::MIN_FREE_SPACE) {
            throw new ModuleUpdates\Exception('disk_full', p__('backoffice', 'Not enough free disk space to download the update.'));
        }

        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $package = $service->download(
            $code,
            $siberianKey,
            $moduleKey,
            $installedVersion,
            $host,
            $tmp,
            (string) $list['manifest']['terms']['hash']
        );

        return $package['path'];
    }

    /**
     * @param ModuleUpdates\Exception $e
     * @return array
     */
    public static function errorPayload(ModuleUpdates\Exception $e)
    {
        $payload = [
            'error' => true,
            'code' => $e->getErrorCode(),
            'message' => $e->getMessage(),
        ];
        $extra = $e->getExtra();
        if (!empty($extra['terms'])) {
            $payload['terms'] = $extra['terms'];
        }

        return $payload;
    }

    /**
     * Cron entry point: refreshes the list once a day and remembers how many updates are waiting.
     *
     * @param $cron
     * @param $task
     */
    public static function cron($cron, $task)
    {
        try {
            $service = self::service();
            if (!$service->isEnabled()) {
                return;
            }
            $list = $service->refresh(true);
            __set('module_updates_available', (string) count($service->pending(self::installed(), $list)));
        } catch (\Exception $e) {
            // The downloader being down must never break the cron run.
            log_warn('module updates: ' . $e->getMessage());
        }
    }
}
