<?php

namespace Siberian;

/**
 * Class Provider
 * @package Siberian
 */
class Provider
{
    /**
     * @var string
     */
    public static $master = 'https://updates02.siberiancms.com/system/providers.json';

    /**
     * @var bool
     */
    public static $debug = false;

    /**
     * @var array
     */
    public static $cached = [];

    /**
     * @return array|mixed
     */
    public static function getMaster ()
    {
        return self::_getContent('master', self::$master);
    }

    /**
     * @return array|mixed
     */
    public static function getLicenses ()
    {
        return self::_getContent('licenses', self::getMaster()['licenses']['url']);
    }

    /**
     * @return array|mixed
     */
    public static function getModules ()
    {
        return self::_getContent('modules', self::getMaster()['modules']['url']);
    }

    /**
     * @return array|mixed
     */
    public static function getUpdates ()
    {
        return self::_getContent('updates', self::getMaster()['updates']['url']);
    }

    /**
     * @return array|mixed
     */
    public static function getSources ()
    {
        return self::_getContent('sources', self::getMaster()['sources']['url']);
    }

    /**
     * @return array|mixed
     */
    public static function getServices ()
    {
        return self::_getContent('services', self::getMaster()['services']['url']);
    }

    /**
     * @return array|mixed
     */
    public static function getApkAabBuilder ()
    {
        return self::getServices()['apk_aab_builder']['url'];
    }

    /**
     * @return array|mixed
     */
    public static function getBackofficeNotification ()
    {
        return self::getServices()['backoffice_notification']['url'];
    }

    /**
     * @return array|mixed
     */
    public static function getWhitelistHosted ()
    {
        return self::_getContent('whitelist_hosted', self::getModules()['whitelist_hosted']['url']);
    }

    /**
     * @param $cacheKey
     * @param $endpoint
     * @return array|mixed
     */
    private static function _getContent ($cacheKey, $endpoint)
    {
        if (!array_key_exists($cacheKey, self::$cached)) {
            try {
                $jsonContent = Request::get($endpoint);
                $content = Json::decode($jsonContent);
            } catch (\Exception $e) {
                $content = [];
            }
            self::$cached[$cacheKey] = $content;
        }
        return self::$cached[$cacheKey];
    }
}
