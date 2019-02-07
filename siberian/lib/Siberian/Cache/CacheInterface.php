<?php

namespace Siberian\Cache;

/**
 * Interface \Siberian\Cache\CacheInterface
 *
 * @version 4.16.0
 *
 */

interface CacheInterface
{
    /**
     * @return mixed
     */
    public static function init();

    /**
     * @param $version
     * @param null $cache
     * @return mixed
     */
    public static function fetch($version, $cache = null);

    /**
     * @return mixed
     */
    public static function preWalk();

    /**
     * @return mixed
     */
    public static function postWalk();
}
