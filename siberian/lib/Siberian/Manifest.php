<?php

namespace Siberian;

/**
 * Class Manifest
 * @package Siberian
 */
class Manifest
{
    /**
     * @param bool $skipCache
     */
    public static function rebuild ($skipCache = false)
    {
        try {
            $mainDomain = __get('main_domain');
            if (empty($mainDomain)) {
                throw new Exception('#958-00: ' .
                    __('Main domain is required, you can set it in Settings > General'));
            }

            echo color('Rebuilding application manifest files.', 'brown') . PHP_EOL;

            if (!$skipCache) {
                echo color('Clearing cache...', 'red') . PHP_EOL;
                Cache::__clearCache();
            }
            
            \Zend_Registry::get('cache')
                ->clean(\Zend_Cache::CLEANING_MODE_ALL);

            $protocol = 'https://';
            Autoupdater::configure($protocol . $mainDomain);

            echo color('Done.', 'brown') . PHP_EOL;
        } catch (\Exception $e) {
            print_r($e->getTrace());
        }
    }
}
