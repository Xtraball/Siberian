<?php

use Zend_Db_Table as DbTable;

/**
 * Class Session_Model_Service
 */
class Session_Model_Service
{
    /**
     * @param null $seconds
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public static function gc($seconds = null)
    {
        // After 7 days session is removed!
        $sessionUntouched = $seconds ?? __get('session_lifetime');
        $now = time();

        echo $sessionUntouched;

        $db = DbTable::getDefaultAdapter();
        $db->query('DELETE FROM session WHERE (modified + :days) < :now;', [
            ':days' => $sessionUntouched,
            ':now' => $now,
        ]);
    }
}
