<?php

namespace Siberian;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \DirectoryIterator;
use \Zend_Registry;

/**
 * Class \Siberian\Cache
 *
 * @version 4.20.27
 *
 * Cache system for inheritance
 *
 * Contains all common references for Siberian_Cache_*
 */
class Cache
{
    /**
     * @var string
     */
    const LOCAL_PATH = 'app/local/';

    /**
     * @var string
     */
    const DEMO_PATH = 'app/demo/';

    /**
     * @var string
     */
    const PE_PATH = 'app/pe/';

    /**
     * @var string
     */
    const MAE_PATH = 'app/mae/';

    /**
     * @var string
     */
    const SAE_PATH = 'app/sae/';

    /**
     * @var array
     */
    public static $caches = [];

    /**
     * @var array
     */
    public static $editions = [
        'sae' => ['sae', 'local'],
        'mae' => ['sae', 'mae', 'local'],
        'pe' => ['sae', 'mae', 'pe', 'local'],
        'demo' => ['sae', 'mae', 'pe', 'demo', 'local'],
    ];

    /**
     * @var array
     */
    public static $registered_caches = [];

    /**
     * @return bool
     */
    public static function init()
    {
        $basePathCache = path(static::CACHE_PATH);
        // Never cache in development!
        if (static::CACHING && is_readable($basePathCache)) {
            $cached = json_decode(file_get_contents($basePathCache), true);
            if (is_null($cached)) {
                // Otherwise run without cache!
                self::run();
                return true;
            }

            if (!empty($cached)) {
                static::$caches[static::CODE] = $cached;
            }
        } else {
            self::run();
            if (static::CACHING) {
                $jsonCache = json_encode(static::$caches[static::CODE]);
                if ($jsonCache !== false) {
                    File::putContents($basePathCache, $jsonCache);
                }
            }
        }
    }

    /**
     * Run the cache builder
     */
    public static function run()
    {
        static::preWalk();
        static::walk();
        static::postWalk();
    }

    /**
     * @param null $code
     * @return bool|mixed
     */
    public static function getCache($code = null)
    {
        $code = (is_null($code)) ? static::CODE : $code;

        if (isset(static::$caches[$code])) {
            return static::$caches[$code];
        }

        return false;
    }

    /**
     * @param $cache
     * @param null $code
     */
    public static function setCache($cache, $code = null)
    {
        $code = (is_null($code)) ? static::CODE : $code;

        static::$caches[$code] = $cache;
    }

    /**
     * Common method for TYPE walkers
     *
     * We refresh only local cache, sae/mae/pe are pre-built for convenience.
     */
    public static function walk()
    {
        static::fetch(self::LOCAL_PATH);
    }

    /**
     *
     */
    public static function clearOpCache()
    {
        try {
            if (function_exists('apc_clear_cache')) {
                apc_clear_cache();
                apc_clear_cache('user');
                apc_clear_cache('opcode');
            }
        } catch (\Exception $e) {
            //
        }

        try {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        } catch (\Exception $e) {
            //
        }
    }

    /**
     *
     */
    public static function clearCache()
    {
        $path = path(static::CACHE_PATH);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Clear a folder cache
     *
     * @deprecated do not use!
     * @param $folder
     */
    public static function __clearFolder($folder)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, 4096),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $filename = $file->getFileName();
            if (!preg_match("/android_pwd|\.gitignore/", $filename) &&
                file_exists($file->getPathName())) {
                unlink($file->getPathName());
            }
        }
    }

    /**
 * @param $pathFromSiberian
 */
    public static function __clearFolderSystem($pathFromSiberian)
    {
        $base = path($pathFromSiberian);
        exec("find {$base}/. -name '*' ! -name 'android_pwd*' ! -name '.gitignore' -delete");
    }

    /**
     * Alias to clear cache
     */
    public static function __clearCache()
    {
        self::__clearFolderSystem('/var/cache');

        Hook::trigger('cache.clear.cache');
    }

    /**
     * Alias to clear cache images
     */
    public static function __clearCacheImages()
    {
        self::__clearFolderSystem('/var/cache_images');

        Hook::trigger('cache.clear.cache_images');
    }

    /**
     * Alias to clear cache
     */
    public static function __clearLog()
    {
        $folder = path('/var/log');

        /** Backup android_pwd files */
        $androidPwdBackup = path('var/apps/android/pwd');
        $logs = new DirectoryIterator($folder);
        foreach ($logs as $log) {
            if (!$log->isDir() && !$log->isDot()) {
                $filename = $log->getFilename();
                if (preg_match("/android_pwd/", $filename)) {
                    copy($log->getPathname(), "{$androidPwdBackup}/{$filename}");
                }
            }
        }

        self::__clearFolderSystem('/var/log');

        Hook::trigger('cache.clear.log');
    }

    /**
     * Clear all locks, or only named ones.
     *
     * @param null $name
     */
    public static function __clearLocks($name = null)
    {
        $folder = path("var/tmp/");

        $locks = new DirectoryIterator($folder);
        foreach ($locks as $lock) {
            if (!$lock->isDir() && !$lock->isDot()) {
                $filename = $lock->getFilename();
                if (preg_match("/\.lock/", $filename)) {
                    if (!empty($name)) {
                        if (preg_match("/$name/", $filename)) {
                            unlink($lock->getPathname());
                        }
                    } else {
                        unlink($lock->getPathname());
                    }
                }
            }
        }

        Hook::trigger('cache.clear.locks');
    }

    /**
     * Alias to clear cache
     */
    public static function __clearTmp()
    {
        self::__clearFolderSystem("/var/tmp");

        Hook::trigger('cache.clear.tmp');
    }

    /**
     * @param bool $cache
     * @return array|mixed
     * @throws \Zend_Exception
     */
    public static function getDiskUsage($cache = false)
    {
        if (!$cache) {
            $cachedValue = __get('disk_usage_cache');
            if (!empty($cachedValue)) {
                return Json::decode($cachedValue);
            }
            return [
                'total' => '-',
                'log_size' => '-',
                'cache_size' => '-',
                'cache_images_size' => '-',
                'tmp_size' => '-',
            ];
        }

        /**
         * @param $start
         * @throws \Siberian\Exception
         */
        function timeout($start)
        {
            if ((time() - $start) > 300) {
                throw new Exception('timelimit hit');
            }
        }

        $total_size = "-";
        $var_log_size = "-";
        $var_cache_size = "-";
        $var_cache_images_size = "-";
        $var_tmp_size = "-";

        try {
            $start = time();

            $var_log = path('var/log');
            exec("du -cksh {$var_log}", $output);
            $parts = explode("\t", end($output));
            $var_log_size = $parts[0];

            timeout($start);

            $var_cache = path('var/cache');
            exec("du -cksh {$var_cache}", $output);
            $parts = explode("\t", end($output));
            $var_cache_size = $parts[0];

            timeout($start);

            $var_cache_images = path('var/cache_images');
            exec("du -cksh {$var_cache_images}", $output);
            $parts = explode("\t", end($output));
            $var_cache_images_size = $parts[0];

            timeout($start);

            $var_tmp = path('var/tmp');
            exec("du -cksh {$var_tmp}", $output);
            $parts = explode("\t", end($output));
            $var_tmp_size = $parts[0];

            timeout($start);

            $total = path('');
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
            'cache_images_size' => $var_cache_images_size,
            'tmp_size' => $var_tmp_size,
        ];
        $encodedResult = Json::encode($result);
        __set('disk_usage_cache', $encodedResult);

        return $result;
    }

    /**
     * @param $feature
     */
    public static function register($feature)
    {
        if (!in_array($feature, self::$registered_caches, true)) {
            self::$registered_caches[] = $feature;
        }
    }

    /**
     * @param $feature
     * @return bool
     */
    public static function isRegistered($feature): bool
    {
        return (in_array($feature, self::$registered_caches, true));
    }

}
