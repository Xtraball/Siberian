<?php

namespace Siberian;

use Zend_Session;
use Zend_Session_SaveHandler_DbTable;
use Zend_Session_SaveHandler_Redis;

/**
 * Class Session
 * @package Siberian
 */
class Session
{
    /**
     * @param array $configSession
     * @throws \Zend_Session_Exception
     * @throws \Zend_Session_SaveHandler_Exception
     */
    public static function init($configSession = [])
    {
        if (__get('session_handler') === 'redis') {
            $redisConfig = [
                'keyPrefix' => __get('redis_prefix'),
                'endpoint' => __get('redis_endpoint')
            ];

            Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_Redis($redisConfig));
        } else {
            $config = [
                'name' => 'session',
                'primary' => 'session_id',
                'modifiedColumn' => 'modified',
                'dataColumn' => 'data',
                'lifetimeColumn' => 'lifetime',
                'lifetime' => 2147483647
            ];

            Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
        }
    }
}
