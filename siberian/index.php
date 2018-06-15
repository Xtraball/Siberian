<?php

/**
 * Siberian
 *
 * @version 4.12.18
 * @author Xtraball SAS <dev@xtraball.com>
 */

global $_config;

$oldUmask = umask(003);

if (!file_exists('./config.php')) {
    copy('./config.sample.php', './config.php');
}

if (!file_exists('./lib/Siberian/Version.php')) {
    copy('./lib/Siberian/Version.sample.php', './lib/Siberian/Version.php');
}

require_once './config.php';

// Php Info!
if (($_config['environment'] === 'development') && isset($_GET['phpi'])) {
    phpinfo();
    die;
}

set_time_limit(300);
ini_set('max_execution_time', 300);
umask(0);

setlocale(LC_MONETARY, 'en_US');

defined('DS')
    || define('DS', DIRECTORY_SEPARATOR);

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/app'));

// Defining ENV globally!
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', $_config['environment']);

// Sourcing default libs!
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../lib'),
)));

require_once 'Zend/Application.php';

// Initializing the application!
$ini = is_readable(APPLICATION_PATH . '/configs/app.ini') ?
    APPLICATION_PATH . '/configs/app.ini' : APPLICATION_PATH . '/configs/app.sample.ini';

$application = new Zend_Application(
    $_config['environment'],
    [
        'config' => [
            $ini,
            APPLICATION_PATH . '/configs/resources.cachemanager.ini',
        ],
    ]
);

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);
Zend_Registry::set('_config', $_config);

session_cache_limiter(false);

// When you need to catch fatal errors create the corresponding congif line `$_config['handle_fatal_errors'] = true;`!
if (isset($_config['handle_fatal_errors']) && $_config['handle_fatal_errors'] === true) {
    // Handle fatal errors!
    function shutdownFatalHandler() {
        $error = error_get_last();
        if($error !== null) {
            ob_clean();
            http_response_code(400);

            $payload = [
                'error' => true,
                'fullError' => $error,
                'message' => 'ERROR: ' . str_replace("\n", ' - ', $error['message']),
            ];

            exit(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    }
    // Handle fatal errors!
    register_shutdown_function('shutdownFatalHandler');
}

// Running!
$application->bootstrap()->run();

// Revert umask!
umask($oldUmask);