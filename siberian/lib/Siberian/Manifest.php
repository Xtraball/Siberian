<?php

namespace Siberian;

/**
 * Class Manifest
 * @package Siberian
 */
class Manifest
{
    /**
     * @throws Exception
     */
    public static function rebuild ()
    {
        $mainDomain = __get('main_domain');
        if (empty($mainDomain)) {
            throw new Exception('#958-00: ' .
                __('Main domain is required, you can set it in Settings > General'));
        }

        echo color('Rebuilding application manifest files.', 'brown') . PHP_EOL;

        \Siberian_Cache::__clearCache();

        \Zend_Registry::get('cache')
            ->clean(\Zend_Cache::CLEANING_MODE_ALL);

        $protocol = "https://";
        \Siberian_Autoupdater::configure($protocol . $mainDomain);

        echo color('Done.', 'brown') . PHP_EOL;
    }
}