<?php
/**
 * Class Siberian_Session
 *
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * Release: <package_version>
 */
class Siberian_Session
{

    /**
     * @param array $configSession
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
