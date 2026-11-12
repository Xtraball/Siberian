<?php
/**
 * The "Session, garbage collector" job is "Session_Model_Service::gc". The scheduler calls a module job with the
 * scheduler and the task, so gc() received the scheduler as its "seconds" argument: it printed it ("Object of class
 * Siberian\Cron could not be converted to string") and the job failed on every run. Only a positive number is taken as
 * the lifetime, anything else gives the configured one, then seven days.
 */
$root = dirname(__DIR__, 3) . '/siberian';
require_once $root . '/app/sae/modules/Session/Model/Service.php';

return [
    'the scheduler object is not a lifetime' => function () {
        t_equal(86400, Session_Model_Service::lifetime(new stdClass(), 86400));
        t_equal(86400, Session_Model_Service::lifetime(new stdClass(), '86400'));
    },
    'an explicit number of seconds wins' => function () {
        t_equal(3600, Session_Model_Service::lifetime(3600, 86400));
        t_equal(3600, Session_Model_Service::lifetime('3600', 86400));
    },
    'nothing usable gives seven days' => function () {
        t_equal(604800, Session_Model_Service::lifetime(null, null));
        t_equal(604800, Session_Model_Service::lifetime(null, ''));
        t_equal(604800, Session_Model_Service::lifetime(0, false));
        t_equal(604800, Session_Model_Service::lifetime(-5, 'abc'));
    },
    'gc() prints nothing' => function () {
        $source = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Session/Model/Service.php');
        t_true(!preg_match('/^\s*echo\s/m', $source), 'a cron job must not echo');
    },
];
