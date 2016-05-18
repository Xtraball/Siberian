<?php

/**
 * Class Siberian_Design
 *
 * @version 4.1.0
 *
 * Adding inheritance system in the design folders
 *
 */

class Siberian_Design
{
    public static $design_cache = array();
    public static $design_codes = array();

    public static $editions = array(
        "sae" => array("sae", "local"),
        "mae" => array("sae", "mae", "local"),
        "pe" => array("sae", "mae", "pe", "local"),
        "demo" => array("sae", "mae", "pe", "demo", "local"),
    );

    /** Various "Edition" Path's */
    const LOCAL_PATH = "app/local/design/";
    const SAE_PATH = "app/sae/design/";
    const MAE_PATH = "app/mae/design/";
    const PE_PATH = "app/pe/design/";
    const DEMO_PATH = "app/demo/design/";
    const CACHE_PATH = "var/cache/design.cache";

    const CACHING = false; /** Caching system is not done. */

    public static function init() {
        $basePathCache = Core_Model_Directory::getBasePathTo(self::CACHE_PATH);
        /** Never cache in development */
        if(self::CACHING && APPLICATION_ENV == "production" && file_exists($basePathCache)) {
            $cached = json_decode(file_get_contents($basePathCache));
            if(!empty($cached)) {
                self::$design_cache = $cached;
            }
        } else {
            /** Registering depending on type */
            switch(Siberian_Version::TYPE) {
                default: case 'SAE':
                        self::registerDesignType(self::SAE_PATH);
                    break;
                case 'MAE':
                        self::registerDesignType(self::SAE_PATH);
                        self::registerDesignType(self::MAE_PATH);
                    break;
                case 'PE':
                        self::registerDesignType(self::SAE_PATH);
                        self::registerDesignType(self::MAE_PATH);
                        self::registerDesignType(self::PE_PATH);
                    break;
            }

            /** DEMO is a special case, it's a PE with additional modules */
            //if(Siberian_Version::isDemo()) {
            //    self::registerDesignType(self::DEMO_PATH);
            //}

            /** Local always on top of everything (user defined) */
            self::registerDesignType(self::LOCAL_PATH);

            if(self::CACHING) {
                file_put_contents($basePathCache, json_encode(self::$design_cache));
            }

        }
    }

    /** @system */
    public static function clearCache() {
        unlink(Core_Model_Directory::getBasePathTo(self::CACHE_PATH));
    }

    public static function registerDesignType($version)
    {
        $design_codes = glob("$version*");

        foreach ($design_codes as $design_code) {

            /** Init the array if not. */
            $base_code = basename($design_code);
            if (!isset(self::$design_cache[$base_code])) {
                self::$design_cache[$base_code] = array();
            }

            /** Looping trough files */
            self::recursiveSearch($design_code, $base_code);
        }
    }

    protected static function recursiveSearch($folder, $base_code) {
        $links = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, 4096),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($links as $link) {
            if(!$link->isDir()) {
                $path = $link->getPathname();
                $named_path = str_replace("$folder/", "", $path);
                self::$design_cache[$base_code][$named_path] = $path;
            }
        }
    }

    public static function getPath($base_file, $type = null, $design_code = null, $key = false) {
        $type = is_null($type) ? APPLICATION_TYPE : $type;
        $design_code = is_null($design_code) ? DESIGN_CODE : $design_code;

        /** Key contain only single slashes, removing duplicates helps to find the right ones. */
        $base_file = preg_replace("#/+#", "/", sprintf("%s/%s", $design_code, $base_file));

        if(isset(self::$design_cache[$type]) && isset(self::$design_cache[$type][$base_file])) {
            return "/".self::$design_cache[$type][$base_file];
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
        if(isset(self::$design_cache[$type])) {
            return self::$design_cache[$type];
        }
        return false;
    }

    public static function getBasePath($base_file, $type = null, $design_code = null) {
        return Core_Model_Directory::getBasePathTo(self::getPath($base_file, $type, $design_code));
    }

}
