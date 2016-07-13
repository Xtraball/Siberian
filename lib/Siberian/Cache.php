<?php

/**
 * Class Siberian_Cache
 *
 * @version 4.1.0
 *
 * Cache system for inheritance
 *
 * Contains all common references for Siberian_Cache_*
 */

class Siberian_Cache
{
    public static $caches = array();

    public static $editions = array(
        "sae"   => array("sae", "local"),
        "mae"   => array("sae", "mae", "local"),
        "pe"    => array("sae", "mae", "pe", "local"),
        "demo"  => array("sae", "mae", "pe", "demo", "local"),
    );

    const LOCAL_PATH    = "app/local/";
    const DEMO_PATH     = "app/demo/";
    const PE_PATH       = "app/pe/";
    const MAE_PATH      = "app/mae/";
    const SAE_PATH      = "app/sae/";

    public static function init() {
        $basePathCache = Core_Model_Directory::getBasePathTo(static::CACHE_PATH);
        /** Never cache in development */
        if(static::CACHING && is_readable($basePathCache)) {
            $cached = json_decode(file_get_contents($basePathCache), true);
            if(is_null($cached)) {
                # Otherwise run without cache.
                self::run();
                return true;
            }

            if(!empty($cached)) {
                static::$caches[static::CODE] = $cached;
            }

        } else {

            self::run();

            if(static::CACHING) {
                $jsonCache = json_encode(static::$caches[static::CODE]);
                if($jsonCache !== false) {
                    file_put_contents($basePathCache, $jsonCache);
                }
            }
        }
    }

    /**
     * Run the cache builder
     */
    public static function run() {
        static::preWalk();
        self::walk();
        static::postWalk();
    }

    /**
     * @param null $code
     * @return bool|mixed
     */
    public static function getCache($code = null) {
        $code = (is_null($code)) ? static::CODE : $code;

        if(isset(static::$caches[$code])) {
            return static::$caches[$code];
        }

        return false;
    }

    /**
     * @param $cache
     * @param null $code
     */
    public static function setCache($cache, $code = null) {
        $code = (is_null($code)) ? static::CODE : $code;

        static::$caches[$code] = $cache;
    }

    /**
     * Common method for TYPE walkers
     */
    public static function walk() {
        /** Registering depending on type */
        switch(Siberian_Version::TYPE) {
            default: case 'SAE':
                static::fetch(self::SAE_PATH);
            break;
            case 'MAE':
                    static::fetch(self::SAE_PATH);
                    static::fetch(self::MAE_PATH);
                break;
            case 'PE':
                    static::fetch(self::SAE_PATH);
                    static::fetch(self::MAE_PATH);
                    static::fetch(self::PE_PATH);
                break;
        }

        /** DEMO is a special case, it's a PE with additional modules */
        //if(Siberian_Version::isDemo()) {
        //    self::registerDesignType(self::DEMO_PATH);
        //}

        /** Local always on top of everything (user defined) */
        static::fetch(self::LOCAL_PATH);
    }

    public static function clearCache() {
        unlink(Core_Model_Directory::getBasePathTo(static::CACHE_PATH));
    }

}
