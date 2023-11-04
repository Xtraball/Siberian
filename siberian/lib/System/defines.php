<?php

global $_config;

if (array_key_exists('cron', $_config)) {
    defined('CRON')
    || define('CRON', $_config['cron']);
}

defined('DS')
|| define('DS', DIRECTORY_SEPARATOR);

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../../app'));

// Defining ENV globally!
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', $_config['environment']);

defined('STDOUT')
|| define('STDOUT', fopen('php://stdout', 'wb'));

defined('STDIN')
|| define('STDIN', fopen('php://stdin', 'rb'));

defined('STDERR')
|| define('STDERR', fopen('php://stderr', 'wb'));
