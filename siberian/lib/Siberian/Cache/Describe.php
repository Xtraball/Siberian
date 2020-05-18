<?php

namespace Siberian\Cache;

use Siberian\Cache as Cache;

use \DirectoryIterator;
use Siberian\File;

/**
 * Class \Siberian\Cache\Describe
 *
 * @version 4.16.11
 *
 * Adding inheritance system in the translations
 */

class Describe extends Cache implements CacheInterface
{
    /**
     *
     */
    const CODE = 'describe';

    /**
     *
     */

    const CACHE_PATH = 'var/cache/table/';

    /**
     *
     */
    const CACHING = true;

    static public $tables = [];

    /**
     * @param $version
     * @param null $cache
     * @return mixed|void
     */
    public static function fetch($version, $cache = null)
    {

    }

    /**
     * @return mixed|void
     */
    public static function preWalk()
    {
        $base = path(static::CACHE_PATH);
        if (!is_dir($base)) {
            mkdir($base, 0777, true);
        }

        $cachedTables = new DirectoryIterator($base);
        foreach ($cachedTables as $cachedTable) {
            if ($cachedTable->isFile()) {
                $meta = json_decode(file_get_contents($cachedTable->getPathname()), true);
                $keyId = $cachedTable->getFilename();

                self::$tables[$keyId] = $meta;
            }
        }
    }

    /**
     * Common method for TYPE walkers
     *
     * We refresh only local cache, sae/mae/pe are pre-built for convenience.
     */
    public static function walk()
    {
    }

    /**
     * @return mixed|void
     */
    public static function postWalk()
    {

    }

    /**
     * @param $meta
     * @param $keyId
     */
    public static function save($meta, $keyId)
    {
        $base = path(static::CACHE_PATH);
        if (!is_dir($base)) {
            mkdir($base, 0777, true);
        }

        self::$tables[$keyId] = $meta;

        $basePathCache = path(static::CACHE_PATH . $keyId);
        if (static::CACHING) {
            $jsonCache = json_encode($meta);
            if ($jsonCache !== false) {
                File::putContents($basePathCache, $jsonCache);
            }
        }
    }
}
