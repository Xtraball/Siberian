<?php

namespace Siberian;

use Zend_Session;
use Zend_Session_SaveHandler_DbTable;

/**
 * Class Session
 * @package Siberian
 */
class Session
{
    /**
     * @param array $configSession
     * @throws \Zend_Db_Table_Exception
     * @throws \Zend_Session_Exception
     */
    public static function init($configSession = [])
    {
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
