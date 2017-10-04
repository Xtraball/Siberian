<?php

/**
 * SiberianCMS
 *
 * @version 4.12.14
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @development fast-env switch
 *
 */

global $_config;

if(!file_exists("./config.php")) {
    copy("./config.sample.php", "./config.php");
}

require_once "./config.php";

set_time_limit(300);
ini_set('max_execution_time', 300);
umask(0);

setlocale(LC_MONETARY, 'en_US');

/** @deprecated from 4.1.0, as we support only linux/unix based servers, this isn't necessary */
defined('DS')
    || define('DS', DIRECTORY_SEPARATOR);

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/app'));

/** Defining ENV globally */
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', $_config['environment']);

/** Sourcing default libs */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../lib'),
)));

require_once 'Zend/Application.php';

/** Initializing the application */
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

// PHPINFO
if(($_config['environment'] === 'development') && isset($_GET['phpi'])) {
    phpinfo();
    die;
}

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);
Zend_Registry::set('_config', $_config);

session_cache_limiter(false);

// Only in development for now.
if (APPLICATION_ENV === 'development') {
    # Handle fatal errors
    function shutdownFatalHandler() {
        $error = error_get_last();
        if($error !== null) {
            ob_clean();
            http_response_code(400);

            $error_msg = str_replace("\n", ' - ', $error['message']);

            $payload = [
                'error' => true,
            ];

            $message = 'An unknown error occured, please try again.';
            $htmlMessage = '<!-- An unknown error occured, please try again. -->';
            if (APPLICATION_ENV === 'development') {
                $message = 'ERROR: ' . $error_msg;
                $htmlMessage = '<!-- ERROR:' . $error_msg . PHP_EOL .
                    print_r($error, true) . PHP_EOL .
                    '-->';
                $payload['fullError'] = $error;
            }

            $payload['message'] = $message;

            // Ajax/Direct request difference
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                exit(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else {
                exit($htmlMessage);
            }

        }
    }
    // Handle fatal errors
    register_shutdown_function('shutdownFatalHandler');
}

// Running
$application->bootstrap()->run();
