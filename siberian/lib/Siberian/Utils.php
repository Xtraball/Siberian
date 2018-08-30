<?php

namespace Siberian;

/**
 * Class Utils
 * @package Siberian
 *
 * @version 4.2.0
 *
 * @author Xtraball <dev@wtraball.com>
 */
class Utils
{

    const EMERG = 0;  // Emergency: system is unusable
    const ALERT = 1;  // Alert: action must be taken immediately
    const CRIT = 2;  // Critical: critical conditions
    const ERR = 3;  // Error: error conditions
    const WARN = 4;  // Warning: warning conditions
    const NOTICE = 5;  // Notice: normal but significant condition
    const INFO = 6;  // Informational: informational messages
    const DEBUG = 7;  // Debug: debug messages

    /**
     * @var \Siberian_Log
     */
    public static $logger;

    /**
     * @var array
     */
    public static $config;

    /**
     * @var integer
     */
    public static $debug_level;

    /**
     * @throws \Zend_Exception
     */
    public static function load()
    {
        define("SAE", 100);
        define("MAE", 200);
        define("PE", 300);
        define("DEMO", 400);

        self::$logger = \Zend_Registry::get("logger");
        self::$config = \Zend_Registry::get("_config");

        $level = (isset(self::$config["debug_level"])) ? self::$config["debug_level"] : "-";
        self::$debug_level = (isset(self::$config["debug_level"])) ? constant("self::{$level}") : self::ERR;
    }

    /**
     * @param $method
     * @param $params
     */
    public static function __callStatic($method, $params)
    {
        if (strpos($method, "log_") === 0 && count($params) > 0) {
            $method = substr($method, 4);
            $level = strtoupper($method);
            $value = constant("self::{$level}");

            # Log only if level meets
            if ($value <= self::$debug_level) {
                # Debug (only when activated.
                \Siberian_Debug::message($params[0], $level);

                # log in the default logger
                self::$logger->{$method}($params[0]);
            }
        }
        # do nothing.
    }

}
