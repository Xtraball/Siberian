<?php

class Siberian_Exec {

    /**
     * @var float
     */
    public static $start_time;

    /**
     * @void
     */
    public static function start() {
        self::$start_time = microtime(true);
    }

    /**
     * @return float
     */
    public static function getCurrentExecutionTime() {
        return (self::$start_time - microtime(true));
    }

    /**
     * @return int max_execution_time in seconds
     */
    public static function getIniMaxExecutionTime() {
        return ini_get('max_execution_time');
    }

    /**
     * @param int $margin
     * @return bool
     */
    public static function willReachMaxExecutionTime($margin = 5) {
        return (
            self::getIniMaxExecutionTime() <=
            self::getCurrentExecutionTime() + $margin
        );
    }

}