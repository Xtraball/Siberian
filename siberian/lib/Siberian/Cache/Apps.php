<?php

/**
 * Class Siberian_Cache_Apps
 *
 * @version 4.2.0
 *
 * Adding inheritance system in the var apps merger
 *
 */

class Siberian_Cache_Apps extends Siberian_Cache implements Siberian_Cache_Interface
{
    const CODE = "assets";
    const CACHE_PATH = "var/cache/assets.cache";
    const CACHING = false;

    public static function fetch($version)
    {
        $version = Core_Model_Directory::getBasePathTo("{$version}modules/");

        $modules_assets = glob("$version*/resources/var/apps/*", GLOB_ONLYDIR);

        $cache = static::getCache();

        foreach ($modules_assets as $asset) {

            $matches = array();
            preg_match("#{$version}(.*)\/resources#", $asset, $matches);

            $module_name = $matches[1];

            /** Init the array if not. */
            $type = basename($asset);
            if (!isset($cache[$type])) {
                $cache[$type] = array();
            }

            $base_path = Core_Model_Directory::getBasePathTo("{$version}resources/var/apps/");
            $base_path = str_replace("modules/resources", "modules/{$module_name}/resources", $base_path);

            /** Looping trough files */
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($asset, 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach($files as $file) {
                if($file->isDir()) {
                    continue;
                }

                switch($type) {
                    case "css": case "js": case "img":
                            if(!isset($cache[$type][$file->getFilename()])) {
                                $cache[$type][$file->getFilename()] = $file->getPathname();
                            }
                        break;
                    case "templates":
                            $base_templates = str_replace($base_path, "", $file->getPathname());
                            if(!isset($cache[$type][$file->getFilename()])) {
                                $cache[$type][$file->getFilename()] = array(
                                    "basepath" => $base_path,
                                    "template" => $base_templates,
                                );
                            }
                        break;
                }

            }
        }

        static::setCache($cache);
    }

    public static function preWalk() {}

    public static function postWalk() {

    }
}
