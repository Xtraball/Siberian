<?php

namespace Siberian\Hook;

/**
 * Class Menu
 * @package Siberian\Hook
 */
class Menu
{
    /**
     * @var array
     */
    public static $backoffice = [];

    /**
     * @var array
     */
    public static $editor = [];

    /**
     * @param $type
     * @param $payload
     * @param null $after
     */
    public static function addMenu ($type, $payload, $after = null)
    {
        // TBD!
    }
}