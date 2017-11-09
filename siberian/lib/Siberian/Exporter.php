<?php

/**
 * Class Siberian_Exporter
 *
 * @id 1000
 *
 * @version 4.6.5
 *
 */

class Siberian_Exporter {

    const FLAVOR = "1.0";
    const MIN_VERSION = "4.7.0";

    /**
     * Declared exporters for features
     *
     * @var array
     */
    public static $registered_exporters = array();

    /**
     * Declared exporter options
     *
     * @var array
     */
    public static $registered_options = array();

    /**
     * @param $feature
     * @param $classname
     */
    public static function register($feature, $classname, $options = null) {
        if(!isset(self::$registered_exporters[$feature])) {
            self::$registered_exporters[$feature] = $classname;

            if($options !== null) {
                self::$registered_options[$feature] = $options;
            }
        }
    }

    /**
     * @param $feature
     * @return bool
     */
    public static function isRegistered($feature) {
        return (isset(self::$registered_exporters[$feature]));
    }

    /**
     * @param $feature
     * @return mixed
     */
    public static function getClass($feature) {
        return self::$registered_exporters[$feature];
    }

    /**
     * @param $feature
     * @return mixed
     */
    public static function hasOptions($feature) {
        return (isset(self::$registered_options[$feature]));
    }

    /**
     * @param $feature
     * @return mixed
     */
    public static function getOptions($feature) {
        return self::$registered_options[$feature];
    }
}
