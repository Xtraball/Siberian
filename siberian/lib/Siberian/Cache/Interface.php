<?php

/**
 * Interface Siberian_Cache_Interface
 *
 * @version 4.1.0
 *
 */

interface Siberian_Cache_Interface
{
    public static function init();
    public static function fetch($version);
    public static function preWalk();
    public static function postWalk();
}
