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
     * @param $whitelabel
     */
    public static function setWhitelabel($whitelabel) {
        self::$whitelabel = $whitelabel;
    }

    /**
     * @return bool|Whitelabel_Model_Editor
     */
    public static function getWhitelabel() {
        return self::$whitelabel;
    }
}