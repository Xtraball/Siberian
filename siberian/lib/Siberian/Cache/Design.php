<?php

namespace Siberian\Cache;

use Siberian\Cache as Cache;
use Siberian\File;
use Siberian\Version as Version;

use \DirectoryIterator;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 * Class \Siberian\Cache\Design
 *
 * @version 4.16.0
 *
 * Adding inheritance system in the design folders
 *
 */
class Design extends Cache implements CacheInterface
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
    public static $module_override = [];

    /**
     * @var string
     */
    const CODE = 'design';

    /**
     * @var boolean
     */
    const CACHING = true;

    /**
     * @var string
     */
    const CACHE_PATH = 'app/local/design/design-cache.json';

    /**
     * @return bool|mixed|void
     */
    public static function init()
    {
        $editionCache = path(static::CACHE_PATH);
        if (!is_file($editionCache)) {
            static::saveCache('local', self::LOCAL_PATH);
        }

        static::run();
    }

    /**
     * @param $version
     * @param null $cache
     * @return bool|mixed|null
     */
    public static function fetch($version, $cache = null)
    {
        if ($cache === null) {
            $cache = static::getCache();
        }

        $base = path('');
        chdir($base);
        $version = "{$version}design/";

        $design_codes = new DirectoryIterator("$version");

        // Core inheritance, with top erase!
        // Also skip deprecated 'mobile' design code, which was used for angular apps!
        foreach ($design_codes as $design_code) {
            if ($design_code->isDir() &&
                !$design_code->isDot() &&
                $design_code->getFilename() !== 'mobile') {

                // Init the array if not!
                $base_code = $design_code->getFilename();
                if (!isset($cache[$base_code])) {
                    $cache[$base_code] = [];
                }

                // Looping trough files!
                $cache = self::recursiveSearch($design_code->getPathname(), $base_code, $cache);
            }
        }

        $modules = str_replace('design', 'modules', $version);
        $module_folders = new DirectoryIterator("$modules");

        // Module loading, without erase (no module should override Core files)!
        foreach ($module_folders as $module_folder) {
            if ($module_folder->isDir() &&
                !$module_folder->isDot() &&
                !is_readable("{$module_folder->getPathname()}/module.disabled") &&
                is_readable("{$module_folder->getPathname()}/resources/design/")) {
                $modules_design_codes = new DirectoryIterator("{$module_folder->getPathname()}/resources/design/");

                $module_name = strtolower(basename($module_folder->getPathname()));
                foreach ($modules_design_codes as $design_code) {
                    if ($design_code->isDir() &&
                        !$design_code->isDot() &&
                        $design_code->getFilename() !== 'mobile') {

                        // Init the array if not!
                        $base_code = $design_code->getFilename();

                        if (!isset($cache[$base_code])) {
                            $cache[$base_code] = [];
                        }

                        // Looping trough files!
                        $cache = self::recursiveSearch(
                            $design_code->getPathname(),
                            $base_code,
                            $cache,
                            self::isAllowedOverride($module_name));
                    }
                }
            }
        }

        return $cache;
    }

    /**
     * @param $type
     */
    public static function rebuild($type)
    {
        echo "CacheDesign::rebuild \n";
        $localType = strtolower($type);
        switch ($localType) {
            default:
            case 'sae':
                static::saveCache('sae', self::SAE_PATH);
                break;
            case 'mae':
                static::saveCache('sae', self::SAE_PATH);
                static::saveCache('mae', self::MAE_PATH);
                break;
            case 'pe':
                static::saveCache('sae', self::SAE_PATH);
                static::saveCache('mae', self::MAE_PATH);
                static::saveCache('pe', self::PE_PATH);
                break;
        }
        echo "CacheDesign::rebuild \n";
    }

    /**
     * @param $type
     * @param $path
     * @return bool|mixed|null
     */
    public static function saveCache ($type, $path)
    {
        // Reset cache!
        $cache = static::fetch($path, []);

        // Write cache!
        $editionCache = path('app/' . $type . '/design/design-cache.json');
        $options = (APPLICATION_ENV === 'development') ? JSON_PRETTY_PRINT : 0;
        try {
            $jsonCache = json_encode($cache, $options);
            if ($jsonCache !== false) {
                File::putContents($editionCache, $jsonCache);
            }
        } catch (\Exception $e) {
            echo "Error::{$e->getMessage()} \n";
            // Something went wrong while saving cache!
        }

        return $cache;
    }

    /**
     * @param $type
     * @param $path
     */
    public static function loadCache ($type, $path)
    {
        $editionCache = path('app/' . $type . '/design/design-cache.json');
        $cachedContent = null;
        try {
            if (is_file($editionCache)) {
                $cache = file_get_contents($editionCache);
                $cachedContent = json_decode($cache, true);
                if ($cachedContent === null) {
                    throw new \Exception(__('Unable to read %s cache file', $editionCache));
                }
            }
        } catch (\Exception $e) {
            // Error!
            $cachedContent = self::saveCache($type, $path);
        }

        $cachedContent = is_array($cachedContent) ? $cachedContent : [];

        $localCache = static::getCache() ? static::getCache() : [];
        $cache = array_replace_recursive($localCache, $cachedContent);

        static::setCache($cache);
    }

    /**
     * Explicitly declaring a module is overriding core design files, allowing then the cache to replace files.
     *
     * @param $module
     */
    public static function overrideCoreDesign($module)
    {
        $module = strtolower($module);
        if (!in_array($module, self::$module_override)) {
            self::$module_override[] = $module;
        }
    }

    /**
     * Check if a module can override design
     *
     * @param $module
     * @return bool
     */
    public static function isAllowedOverride($module)
    {
        $module = strtolower($module);
        $is_allowed = in_array($module, self::$module_override);

        return $is_allowed;
    }

    /**
     *
     */
    public static function preWalk()
    {
        // Load edition pre-built caches
        $localType = strtolower(Version::TYPE);
        switch ($localType) {
            default:
            case 'sae':
                static::loadCache('sae', self::SAE_PATH);
                break;
            case 'mae':
                static::loadCache('sae', self::SAE_PATH);
                static::loadCache('mae', self::MAE_PATH);
                break;
            case 'pe':
                static::loadCache('sae', self::SAE_PATH);
                static::loadCache('mae', self::MAE_PATH);
                static::loadCache('pe', self::PE_PATH);
                break;
        }
    }

    /**
     * Common method for TYPE walkers
     *
     * We refresh only local cache, sae/mae/pe are pre-built for convenience.
     */
    public static function walk()
    {
        static::loadCache('local', self::LOCAL_PATH);
    }

    /**
     *
     */
    public static function postWalk()
    {
    }

    /**
     * @param $folder
     * @param $base_code
     * @param $cache
     * @param bool $replace
     * @return mixed
     */
    protected static function recursiveSearch($folder, $base_code, $cache, $replace = true)
    {
        # 4608 > 4096:Skip_dots, 512:Follow_symlinks
        $links = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, 4608),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($links as $link) {
            if (!$link->isDir() && $link->isFile()) {
                $path = $link->getPathname();
                $named_path = str_replace("$folder/", "", $path);

                if (strpos($named_path, 'siberian/') === 0) {
                    continue;
                }

                if ($replace) {
                    $cache[$base_code][$named_path] = $path;
                } else if (!$replace && !isset($cache[$base_code][$named_path])) {
                    $cache[$base_code][$named_path] = $path;
                }
            }
        }

        return $cache;
    }

    /**
     * @param $base_file
     * @param null $type
     * @param null $design_code
     * @param bool $key
     * @return bool|string
     */
    public static function getPath($base_file, $type = null, $design_code = null, $key = false)
    {
        $cache = static::getCache();

        $type = is_null($type) ? APPLICATION_TYPE : $type;
        $design_code = is_null($design_code) ? DESIGN_CODE : $design_code;

        /** Key contain only single slashes, removing duplicates helps to find the right ones. */
        if ($type == 'email') {
            $base_file = preg_replace("#/+#", "/", sprintf("%s", $base_file));
        } else {
            $base_file = preg_replace("#/+#", "/", sprintf("%s/%s", $design_code, $base_file));
        }

        if (isset($cache[$type]) && isset($cache[$type][$base_file])) {
            return "/" . $cache[$type][$base_file];
        }
        return false;
    }

    /**
     * @param $folder
     * @param null $type
     * @param null $design_code
     * @return array
     */
    public static function searchForFolder($folder, $type = null, $design_code = null)
    {
        $found_files = [];

        $type = is_null($type) ? APPLICATION_TYPE : $type;
        $design_code = is_null($design_code) ? DESIGN_CODE : $design_code;

        $files = self::getType($type);
        $base_folder = preg_replace("#/+#", "/", sprintf("%s/%s", $design_code, $folder));

        foreach ($files as $key => $value) {
            if (strpos($key, $base_folder) === 0) {
                $found_files[$key] = $value;
            }
        }

        return $found_files;
    }

    /**
     * @param $type
     * @return bool
     */
    public static function getType($type)
    {
        $cache = static::getCache();

        if (isset($cache[$type])) {
            return $cache[$type];
        }
        return false;
    }

    /**
     * @param $base_file
     * @param null $type
     * @param null $design_code
     * @return string
     */
    public static function getBasePath($base_file, $type = null, $design_code = null)
    {
        return path(self::getPath($base_file, $type, $design_code));
    }
}
