<?php

namespace Siberian;

/**
 * Class Logger
 * @package Siberian
 */
class Logger
{
    /**
     * @param $message
     */
    public static function info ($message)
    {
        $message = sprintf("[\\Siberian\\Logger::info: %s]: %s\n", date("Y-m-d H:i:s"), $message);
        if (defined('CRON')) {
            echo $message;
        } else {
            $logger = \Zend_Registry::get('logger');
            $logger->info($message);
        }
    }
}