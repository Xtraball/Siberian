<?php

/**
 * Class Siberian_Cache_Design
 *
 * @version 4.2.0
 *
 * Adding inheritance system in the design folders
 *
 */

class Siberian_Cache_Design extends Siberian_Cache implements Siberian_Cache_Interface
{
    /**
     * @var
     */
    public static $design_codes;

    /**
     * Listing what modules are overriding core design
     *
     * @var array
     */
    public static $module_override = array();

    const CODE = "design";
    const CACHE_PATH = "var/cache/design.cache";
    const CACHING = true;

    /**
     * @param $version
     */
    public static function fetch($version) {
        $base = Core_Model_Directory::getBasePathTo("");
        chdir($base);
        $version = "{$version}design/";

        $design_codes = new DirectoryIterator("$version");

        /** Core inheritance, with top erase */
        foreach ($design_codes as $design_code) {
            if($design_code->isDir() && !$design_code->isDot()) {
                $cache = static::getCache();

                /** Init the array if not. */
                $base_code = $design_code->getFilename();
                if (!isset($cache[$base_code])) {
                    $cache[$base_code] = array();
                }

                /** Looping trough files */
                self::recursiveSearch($design_code->getPathname(), $base_code);
            }
        }

        $modules = str_replace("design", "modules", $version);
        $module_folders = new DirectoryIterator("$modules");

        /** Module loading, without erase (no module should override Core files) */
        foreach ($module_folders as $module_folder) {
            if($module_folder->isDir() && !$module_folder->isDot() && is_readable("{$module_folder->getPathname()}/resources/design/")) {
                $modules_design_codes = new DirectoryIterator("{$module_folder->getPathname()}/resources/design/");

                $module_name = strtolower(basename($module_folder->getPathname()));

                foreach ($modules_design_codes as $design_code) {
                    if($design_code->isDir() && !$design_code->isDot()) {
                        $cache = static::getCache();

                        /** Init the array if not. */
                        $base_code = $design_code->getFilename();
                        if (!isset($cache[$base_code])) {
                            $cache[$base_code] = array();
                        }

                        /** Looping trough files */
                        self::recursiveSearch($design_code->getPathname(), $base_code, self::isAllowedOverride($module_name));
                    }
                }
            }
        }
    }

    /**
     * Explicitly declaring a module is overriding core design files, allowing then the cache to replace files.
     *
     * @param $module
     */
    public static function overrideCoreDesign($module) {
        $module = strtolower($module);
        if(!in_array($module, self::$module_override)) {
            self::$module_override[] = $module;
        }
    }

    /**
     * Check if a module can override design
     *
     * @param $module
     * @return bool
     */
    public static function isAllowedOverride($module) {
        $module = strtolower($module);
        $is_allowed = in_array($module, self::$module_override);

        return $is_allowed;
    }

    public static function preWalk() {

    }

    public static function postWalk() {

    }

    protected static function recursiveSearch($folder, $base_code, $replace = true) {
        $cache = static::getCache();

        # 4608 > 4096:Skip_dots, 512:Follow_symlinks
        $links = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, 4608),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($links as $link) {
            if(!$link->isDir() && $link->isFile()) {
                $path = $link->getPathname();
                $named_path = str_replace("$folder/", "", $path);

                if($replace) {
                    $cache[$base_code][$named_path] = $path;
                } else if(!$replace && !isset($cache[$base_code][$named_path])) {
                    $cache[$base_code][$named_path] = $path;
                }

            }
        }
        
        static::setCache($cache);
    }

    public static function getPath($base_file, $type = null, $design_code = null, $key = false) {
        $cache = static::getCache();

        $type = is_null($type) ? APPLICATION_TYPE : $type;
        $design_code = is_null($design_code) ? DESIGN_CODE : $design_code;

        /** Key contain only single slashes, removing duplicates helps to find the right ones. */
        if($type == 'email') {
            $base_file = preg_replace("#/+#", "/", sprintf("%s", $base_file));
        } else {
            $base_file = preg_replace("#/+#", "/", sprintf("%s/%s", $design_code, $base_file));
        }

        if(isset($cache[$type]) && isset($cache[$type][$base_file])) {
            return "/".$cache[$type][$base_file];
        }
        return false;
    }

    public static function searchForFolder($folder, $type = null, $design_code = null) {
        $found_files = array();

        $type = is_null($type) ? APPLICATION_TYPE : $type;
        $design_code = is_null($design_code) ? DESIGN_CODE : $design_code;

        $files = self::getType($type);
        $base_folder = preg_replace("#/+#", "/", sprintf("%s/%s", $design_code, $folder));

        foreach($files as $key => $value) {
            if(strpos($key, $base_folder) === 0) {
                $found_files[$key] = $value;
            }
        }

        return $found_files;
    }

    public static function getType($type) {
        $cache = static::getCache();

        if(isset($cache[$type])) {
            return $cache[$type];
        }
        return false;
    }

    public static function getBasePath($base_file, $type = null, $design_code = null) {
        return Core_Model_Directory::getBasePathTo(self::getPath($base_file, $type, $design_code));
    }
}
