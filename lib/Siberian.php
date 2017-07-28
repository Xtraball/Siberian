<?php

/**
 * Class Siberian
 *
 * Default Siberian wrapper class for static purpose & configurations
 */
class Siberian {

    /**
     * @var bool|Whitelabel_Model_Editor
     */
    public static $whitelabel = false;

    /**
     * @var bool|Application_Model_Application
     */
    public static $application = false;

    /**
     * @param $whitelabel
     */
    public static function setWhitelabel($whitelabel) {
        self::$whitelabel = $whitelabel;
    }

    /**
     * @return bool|Application_Model_Application
     */
    public static function getWhitelabel() {
        return self::$whitelabel;
    }

    /**
     * @param $application
     */
    public static function setApplication($application) {
        self::$application = $application;
    }

    /**
     * @return bool|Application_Model_Application
     */
    public static function getApplication() {
        return self::$application;
    }
}