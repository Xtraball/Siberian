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

    public static $registered_caches = array();

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
        $path = Core_Model_Directory::getBasePathTo(static::CACHE_PATH);
        if(file_exists($path)) {
            unlink($path);
        }

    }

    /**
     * Clear a folder cache
     *
     * @param $folder
     */
    public static function __clearFolder($folder) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, 4096), RecursiveIteratorIterator::SELF_FIRST);
        foreach($files as $file) {
            $filename = $file->getFileName();
            if(!preg_match("/android_pwd|\.gitignore/", $filename) && file_exists($file->getPathName())) {
                unlink($file->getPathName());
            }
        }
    }

    /**
     * Alias to clear cache
     */
    public static function __clearCache() {
        $folder = Core_Model_Directory::getBasePathTo("var/cache/");

        return self::__clearFolder($folder);
    }

    /**
     * Alias to clear cache
     */
    public static function __clearLog() {
        $folder = Core_Model_Directory::getBasePathTo("var/log/");

        /** Backup android_pwd files */
        $android_pwd_backup = Core_Model_Directory::getBasePathTo("var/apps/android/pwd");
        $logs = new DirectoryIterator($folder);
        foreach ($logs as $log) {
            if(!$log->isDir() && !$log->isDot()) {
                $filename = $log->getFilename();
                if(preg_match("/android_pwd/", $filename)) {
                    copy($log->getPathname(), "{$android_pwd_backup}/{$filename}");
                }
            }
        }

        return self::__clearFolder($folder);
    }

    /**
     * Clear all locks, or only named ones.
     *
     * @param null $name
     */
    public static function __clearLocks($name = null) {
        $folder = Core_Model_Directory::getBasePathTo("var/tmp/");

        $locks = new DirectoryIterator($folder);
        foreach ($locks as $lock) {
            if(!$lock->isDir() && !$lock->isDot()) {
                $filename = $lock->getFilename();
                if(preg_match("/\.lock/", $filename)) {
                    if(!empty($name)) {
                        if(preg_match("/$name/", $filename)) {
                            unlink($lock->getPathname());
                        }
                    } else {
                        unlink($lock->getPathname());
                    }
                }
            }
        }
    }

    /**
     * Alias to clear cache
     */
    public static function __clearTmp() {
        $folder = Core_Model_Directory::getBasePathTo("var/tmp/");

        return self::__clearFolder($folder);
    }

    /**
     * Fetch disk usage
     *
     * @param bool $cache
     * @param int $limit
     * @return array|mixed
     */
    public static function getDiskUsage($cache = false) {
        if(!$cache) {
            $cachedValue = System_Model_Config::getValueFor('disk_usage_cache');
            if(!empty($cachedValue)) {
                return Siberian_Json::decode($cachedValue);
            }
            return [
                'total' => '-',
                'log_size' => '-',
                'cache_size' => '-',
                'tmp_size' => '-',
            ];
        } else {
            function timeout($start) {
                if((time() - $start) > 300) {
                    throw new Siberian_Exception('timelimit hit');
                }
            }

            $total_size = "-";
            $var_log_size = "-";
            $var_cache_size = "-";
            $var_tmp_size = "-";

            try {
                $start = time();

                $var_log = Core_Model_Directory::getBasePathTo('var/log');
                exec("du -cksh {$var_log}", $output);
                $parts = explode("\t", end($output));
                $var_log_size = $parts[0];

                timeout($start);

                $var_cache = Core_Model_Directory::getBasePathTo('var/cache');
                exec("du -cksh {$var_cache}", $output);
                $parts = explode("\t", end($output));
                $var_cache_size = $parts[0];

                timeout($start);

                $var_tmp = Core_Model_Directory::getBasePathTo('var/tmp');
                exec("du -cksh {$var_tmp}", $output);
                $parts = explode("\t", end($output));
                $var_tmp_size = $parts[0];

                timeout($start);

                $total = Core_Model_Directory::getBasePathTo('');
                exec("du -cksh {$total}", $output);
                $parts = explode("\t", end($output));
                $total_size = $parts[0];

            } catch (Exception $e) {
                $logger = Zend_Registry::get('logger');
                $logger->info(
                    'Siberian_Cache::getDiskUsage() timeout ' .
                    $e->getMessage()
                );
            }

            $result = [
                'total' => $total_size,
                'log_size' => $var_log_size,
                'cache_size' => $var_cache_size,
                'tmp_size' => $var_tmp_size,
            ];
            $encodedResult = Siberian_Json::encode($result);
            System_Model_Config::setValueFor('disk_usage_cache', $encodedResult);

            return $result;
        }
    }

    /**
     * @param $feature
     */
    public static function register($feature) {
        if(!in_array($feature, self::$registered_caches)) {
            self::$registered_caches[] = $feature;
        }
    }

    /**
     * @param $feature
     * @return bool
     */
    public static function isRegistered($feature) {
        return (in_array($feature, self::$registered_caches));
    }

}
