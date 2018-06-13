<?php

/**
 * Interface Siberian_Cache_Interface
 *
 * @version 4.14.0
 *
 */

interface Siberian_Cache_Interface
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
