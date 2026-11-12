<?php

/**
 * The installation of a platform update, once its files are copied: the modules that are newer than the database are
 * installed, their data inserted, the templates refreshed and the caches rebuilt.
 *
 * Installer_Backoffice_ModuleController::installAction() and `php cli update` both call it, so the two ways of updating
 * run the same steps in the same order.
 */
class Installer_Model_Installer_Update
{
    /**
     * @var array the modules installed by the last call of installPending()
     */
    public static $modules = [];

    /**
     * The update server client of this installation: its edition, version, channel and licence.
     *
     * @param string|null $host the domain sent with the licence key (the back office sends the one of the request)
     * @return \Siberian\Update\Platform
     */
    public static function platform(?string $host): \Siberian\Update\Platform
    {
        return new \Siberian\Update\Platform(
            Siberian_Version::TYPE,
            Siberian_Version::VERSION,
            __get('update_channel'),
            System_Model_Config::getValueFor('siberiancms_key'),
            $host,
            [
                'post' => static function (string $url, array $fields, int $timeout): array {
                    $body = \Siberian\Request::post($url, $fields, null, null, null, ['timeout' => $timeout]);

                    return [\Siberian\Request::$statusCode, $body];
                },
                'write' => static function (string $path, string $content): void {
                    \Siberian\File::putContents($path, $content);
                },
                'translate' => static function (string $text): string {
                    return __($text);
                },
            ]);
    }

    /**
     * Can the files of the package be written by the web user?
     *
     * @param string $file the package (a path inside var/tmp)
     * @throws \Siberian\Exception with the list of the files that are not writable
     */
    public static function checkPermissions(string $file): void
    {
        $parser = new Installer_Model_Installer_Module_Parser();
        $isOk = $parser->setFile($file)->checkPermissions();

        if (!$isOk) {
            throw new \Siberian\Exception(__(implode_polyfill("\n", $parser->getErrors())));
        }
    }

    /**
     * Copies the files of the package over the installation (and removes the ones the package replaces).
     *
     * @param string $file the package (a path inside var/tmp)
     * @throws \Siberian\Exception
     */
    public static function copyPackage(string $file): void
    {
        $parser = new Installer_Model_Installer_Module_Parser();
        if (!$parser->setFile($file)->copy()) {
            throw new \Siberian\Exception(__(implode_polyfill("\n", $parser->getErrors())));
        }
    }

    /**
     * The modules to install: the ones that can be updated, plus the one asked to be reinstalled when that is allowed.
     *
     * @param string $reinstall the module to reinstall, empty for none (opt-in only)
     * @return array module names
     */
    public static function pendingModules(string $reinstall = ''): array
    {
        $modules = [];
        // ZF1-OK: the module list comes from the ZF1 dispatcher, moved as it was from the back office controller
        $moduleNames = Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories();
        foreach ($moduleNames as $moduleName) {
            $module = new Installer_Model_Installer_Module();
            $module->prepare($moduleName);
            if ($module->canUpdate()) {
                $modules[] = $module->getName();
            } else if ($reinstall !== '' &&
                $moduleName === $reinstall &&
                Installer_Model_Installer_Reinstall::isAllowed(
                    $module->isInstalled(),
                    $module->getVersion(),
                    $module->getLastVersion())) {
                $modules[] = $module->getName();
            }
        }

        return array_unique($modules);
    }

    /**
     * Installs what the copied files brought.
     *
     * @param string $reinstall the module to reinstall, empty for none
     * @param callable|null $tick called after each step, may throw to stop and resume later (the back office uses it
     *                            to stop before the PHP time limit and ask the browser to call again)
     * @return array the installed module names
     * @throws Exception
     */
    public static function installPending(string $reinstall = '', ?callable $tick = null): array
    {
        // ZF1-OK: the application cache is the ZF1 registry entry, moved as it was from the back office controller
        $cache = Zend_Registry::isRegistered('cache') ? Zend_Registry::get('cache') : null;
        if ($cache) {
            // ZF1-OK: same cache object
            $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }

        $cacheIds = ['js_mobile.js', 'js_desktop.js', 'css_mobile.css', 'css_desktop.css'];
        foreach ($cacheIds as $cacheId) {
            if (file_exists(Core_Model_Directory::getCacheDirectory(true) . "/{$cacheId}")) {
                unlink(Core_Model_Directory::getCacheDirectory(true) . "/{$cacheId}");
            }
        }

        self::$modules = self::pendingModules($reinstall);
        $tick = $tick ?? static function () {
        };

        $installers = [];
        foreach (self::$modules as $module) {
            $installer = new Installer_Model_Installer();
            $installer
                ->setModuleName($module)
                ->setReinstall($reinstall !== '' && $module === $reinstall)
                ->install();

            $installers[] = $installer;

            $tick($module);
        }

        foreach ($installers as $installer) {
            $installer->insertData();

            $tick($installer->getModuleName());
        }

        // Try installing fresh template
        $installer = new Installer_Model_Installer();
        $installer
            ->setModuleName('Template')
            ->install();

        // Clear cache
        Siberian_Cache_Design::clearCache();
        Siberian_Cache_Translation::clearCache();
        Siberian_Minify::clearCache();

        return self::$modules;
    }

    /**
     * What follows a finished installation: the release marker, design, assets, translations and minified files are
     * rebuilt, and the cache builder cron is told the address of the installation.
     *
     * @param string $baseUrl protocol and host of the installation, empty when unknown (the cron keeps its options)
     * @throws Exception
     */
    public static function finalize(string $baseUrl): void
    {
        Siberian_Autoupdater::configure();

        $cronModel = new Cron_Model_Cron();
        $cachebuilder = $cronModel->find('cachebuilder', 'command');

        if ($cachebuilder->getId() && $baseUrl !== '') {
            $cachebuilder->setOptions(Siberian_Json::encode(['host' => $baseUrl]))->save();
            $cachebuilder->enable();
        }
    }
}
