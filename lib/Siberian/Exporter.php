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

    public static $registered_exporters = array();

    /**
     * @param $feature
     * @param $classname
     */
    public static function register($feature, $classname) {
        if(!isset(self::$registered_exporters[$feature])) {
            self::$registered_exporters[$feature] = $classname;
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
}
