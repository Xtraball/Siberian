<?php

/**
 * Siberian
 *
 * @version 5.0.8
 * @author Xtraball SAS <dev@xtraball.com>
 */

chdir(__DIR__);

require_once __DIR__ . '/lib/System/polyfills.php';

global $_config;

$oldUmask = umask(0);

if (!file_exists('./config.php')) {
    copy('./config.sample.php', './config.php');
}

function dbg()
{
    $args = func_get_args();
    foreach ($args as $arg) {
        file_put_contents(
            __DIR__ . '/var/tmp/debug.log',
            date('d/m/Y H:i:s') . ': ' . print_r($arg, true) . PHP_EOL,
            FILE_APPEND);
    }
}

require_once './config.php';
$_config["cron"] = true;

if (isset($argv) && isset($argv[1]) && ($argv[1] === 'test')) {
    die('OK');
}

if (
    (isset($_ENV['SHELL']) && !empty($_ENV['SHELL'])) ||
    (isset($_SERVER['SHELL']) && !empty($_SERVER['SHELL'])) ||
    php_sapi_name() === 'cli'
) {
    # Continue
} else {
    if (isset($_GET['cron_secret']) &&
        isset($_config['cron_secret']) &&
        ($_GET['cron_secret'] === $_config['cron_secret'])) {
        # Continue
    } else {
        die('You must run from CLI.' . PHP_EOL);
    }
}

require_once __DIR__ . '/lib/System/defines.php';

set_include_path(implode_polyfill(PATH_SEPARATOR, [
    dirname(APPLICATION_PATH) . '/lib',
]));

require_once 'Zend/Application.php';

$application = new Zend_Application(
    $_config['environment'],
    [
        'config' => [
            APPLICATION_PATH . '/configs/app.ini',
            APPLICATION_PATH . '/configs/resources.cachemanager.ini',
        ],
        'bootstrap' => [
            'path' => APPLICATION_PATH . '/BootstrapCron.php',
            'class' => 'BootstrapCron',
        ],
    ]
);

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);
Zend_Registry::set('_config', $_config);

/** Init bootstrap */
$application->bootstrap();

/** Run cron */
$start = time();
$interval = __get('cron_interval');
$interval = (($interval >= 10) && ($interval < 51)) ?
    $interval : false;

defined('DESIGN_CODE') || define('DESIGN_CODE', design_code());

/** Creating an Alert in the Backoffice rather than killing the process */
if (version_compare(PHP_VERSION, '5.6.0') < 0) {
    $description = 'PHP version >= 5.6.0 is required for the cron scheduler to run correctly, your php-cli version is ' .
        PHP_VERSION . '.';

    $notification = new Backoffice_Model_Notification();
    $notification
        ->setTitle(__('Alert: PHP version >= 5.6.0 is required for the cron scheduler to run correctly'))
        ->setDescription(__($description))
        ->setSource('cron')
        ->setType('alert')
        ->setIsHighPriority(1)
        ->setObjectType('cron_scheduler')
        ->setObjectId('42')
        ->save();

    die('PHP >=5.6 is required.\n');
}

if (version_compare(PHP_VERSION, '7.0') < 0) {
    $description = 'PHP version >= 7.0 is required for the cron scheduler to run correctly, your php-cli version is ' .
        PHP_VERSION . '.';

    $notification = new Backoffice_Model_Notification();
    $notification
        ->setTitle(__('Alert: PHP version >= 7.0 is required for the cron scheduler to run correctly'))
        ->setDescription(__($description))
        ->setSource('cron')
        ->setType('alert')
        ->setIsHighPriority(1)
        ->setObjectType('cron_scheduler')
        ->setObjectId('4242')
        ->save();
}

$cron = new Siberian_Cron();
if (isset($argv) && isset($argv[1]) && ($argv[1] === 'runcommand')) {
    if (isset($argv[2])) {
        $cron->runTaskByCommand($argv[2]);
        die('Execution done.' . PHP_EOL);
    }
    die('Missing command name.' . PHP_EOL);
}
$cron->triggerAll();

/** Highly experimental, may increase server load. */
if ($interval !== false) {
    $new_time = time() - $start;
    $loop = 0;
    while ($new_time < 55) {
        sleep($interval);
        $cron->log('Interval repeat ' . ++$loop);
        $cron->triggerAll();
        $new_time = time() - $start;
    }
}

// Revert umask!
umask($oldUmask);
