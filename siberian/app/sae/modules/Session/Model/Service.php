<?php

use Zend_Db_Table as DbTable;

/**
 * Class Session_Model_Service
 */
class Session_Model_Service
{
    /**
     * The scheduler calls a "Class::method" job with the scheduler and the task, so the first argument is not the
     * number of seconds unless somebody called gc(604800) by hand. Anything but a positive number gives the
     * configured lifetime.
     *
     * @param mixed $seconds
     * @param mixed $configured
     * @return int
     */
    public static function lifetime($seconds, $configured)
    {
        foreach ([$seconds, $configured] as $value) {
            if ((is_int($value) || (is_string($value) && ctype_digit($value))) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return 604800;
    }

    /**
     * @param mixed $seconds seconds of inactivity, or what the scheduler gives (ignored)
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public static function gc($seconds = null)
    {
        // After 7 days session is removed!
        $sessionUntouched = self::lifetime($seconds, __get('session_lifetime'));
        $now = time();

        $db = DbTable::getDefaultAdapter();
        $db->query('DELETE FROM session WHERE (modified + :days) < :now;', [
            ':days' => $sessionUntouched,
            ':now' => $now,
        ]);
    }
}
