<?php

/**
 * Class Application_Model_Cron
 */
class Application_Model_Cron
{
    /**
     * @param $cron
     * @param $task
     * @throws \Zend_Session_Exception
     * @throws \Zend_Session_SaveHandler_Exception
     */
    public static function run ($cron, $task)
    {
        $shouldRun = __get('pre-init-application-cache');
        if ($shouldRun === 'yes') {
            // Instant lock!
            __set('pre-init-application-cache', 'no');

            // Running the pre-init!
            \Front_Controller_Api_Base::preInitApplications();
        }
    }

    /**
     *
     */
    public static function triggerRun ()
    {
        __set('pre-init-application-cache', 'yes');
    }
}
