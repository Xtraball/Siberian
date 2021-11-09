<?php

namespace Siberian\Cache;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Siberian\Cache as Cache;

/**
 * Class Siberian\Cache_Apps
 *
 * @version 4.20.25
 *
 * Adding inheritance system in the var apps merger
 *
 */
class Apps extends Cache implements CacheInterface
{
    const CODE = 'assets';
    const CACHE_PATH = 'var/cache/assets.cache';
    const CACHING = false;

    public static function fetch($version, $cache = null)
    {
        $version = path("{$version}modules/");

        $modules_assets = glob("$version*/resources/var/apps/*", GLOB_ONLYDIR);

        $cache = static::getCache();

        foreach ($modules_assets as $asset) {

            $matches = [];
            preg_match("#{$version}(.*)\/resources#", $asset, $matches);

            $module_name = $matches[1];

            // Skip disabled module!
            if (method_exists(new \Installer_Model_Installer_Module(), 'sGetIsEnabled') &&
                !\Installer_Model_Installer_Module::sGetIsEnabled($module_name)) {
                continue;
            }

            /** Init the array if not. */
            $type = basename($asset);
            if (!isset($cache[$type])) {
                $cache[$type] = [];
            }

            $base_path = path("{$version}resources/var/apps/");
            $base_path = str_replace('modules/resources', "modules/{$module_name}/resources", $base_path);

            /** Looping trough files */
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($asset, 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }

                switch ($type) {
                    case 'css':
                    case 'js':
                    case 'img':
                        if (!isset($cache[$type][$file->getFilename()])) {
                            $cache[$type][$file->getFilename()] = $file->getPathname();
                        }
                        break;
                    case 'templates':
                        $base_templates = str_replace($base_path, "", $file->getPathname());
                        if (!isset($cache[$type][$file->getFilename()])) {
                            $cache[$type][$file->getFilename()] = [
                                'basepath' => $base_path,
                                'template' => $base_templates,
                            ];
                        }
                        break;
                }

            }
        }

        static::setCache($cache);
    }

    public static function preWalk()
    {
    }

    public static function postWalk()
    {

    }
}
