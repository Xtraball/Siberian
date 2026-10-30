<?php

use Siberian\ErrorReporter;
use Siberian\ModuleUpdates\ConfigStore;
use Siberian\ModuleUpdates\CurlTransport;

/**
 * Class Core_Model_ErrorReports
 *
 * Glue between the application (system_config, install root, versions, cron) and the framework-free
 * Siberian\ErrorReporter. See docs/error-reports.md.
 */
class Core_Model_ErrorReports
{
    /**
     * @return ErrorReporter
     */
    public static function reporter()
    {
        return new ErrorReporter(
            Core_Model_Directory::getBasePathTo('var/cache/error-reporter.json'),
            Core_Model_Directory::getBasePathTo(''),
            null,
            [self::class, 'isEnabled']
        );
    }

    /**
     * On unless an administrator turned it off. Read when the buffer is written and when it is sent, so a
     * switch takes effect at once and nothing is kept while it is off.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        try {
            return (string) __get('error_reports_enabled') !== '0';
        } catch (\Throwable $e) {
            // No database yet (installer, broken config): nothing is kept.
            return false;
        }
    }

    /**
     * Called from the bootstrap, before the database is available. Never throws.
     */
    public static function boot()
    {
        try {
            self::reporter()->install();
        } catch (\Throwable $e) {
            // never get in the way
        }
    }

    /**
     * @return array
     */
    public static function install()
    {
        return [
            'edition' => \Siberian\Version::TYPE,
            'siberian' => \Siberian\Version::VERSION,
            'php' => PHP_VERSION,
        ];
    }

    /**
     * Cron entry point, every hour at a time that differs per installation.
     *
     * @param $cron
     * @param $task
     */
    public static function cron($cron, $task)
    {
        try {
            if (!self::isEnabled()) {
                // Nothing is kept while it is off.
                self::reporter()->take();

                return;
            }
            $result = self::reporter()->push(
                new CurlTransport(),
                new ConfigStore(),
                self::install(),
                // Only the configuration file can point somewhere else (staging, tests), never the database.
                __getConfig('error_reports_base_url') ?: ErrorReporter::BASE_URL
            );
            if (in_array($result, ['retry', 'dropped'], true)) {
                log_warn('error reports: ' . $result);
            }
        } catch (\Throwable $e) {
            // The downloader being down must never break the cron run.
            log_warn('error reports: ' . $e->getMessage());
        }
    }
}
