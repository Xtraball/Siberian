<?php

/**
 * Siberian
 *
 * @version 4.18.3
 * @author Xtraball SAS <dev@xtraball.com>
 */

global $_config;

$here = __DIR__;
putenv("TMP={$here}/var/tmp");

$oldUmask = umask(003);

if (!file_exists('./config.php')) {
    copy('./config.sample.php', './config.php');
}

if (!file_exists('./lib/Siberian/Version.php')) {
    copy('./lib/Siberian/Version.sample.php', './lib/Siberian/Version.php');
}

require_once './config.php';

// PHP Info!
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
|| define('APPLICATION_PATH', realpath(__DIR__ . '/app'));

// Defining ENV globally!
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', $_config['environment']);

// Sourcing default libs!
set_include_path(implode(PATH_SEPARATOR, [
    dirname(APPLICATION_PATH) . '/lib',
]));

/**
 *
 */
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

// When you need to catch fatal errors create the corresponding config line `$_config["handle_fatal_errors"] = true;`!
if (isset($_config['handle_fatal_errors']) &&
    $_config['handle_fatal_errors'] === true) {
    // Handle fatal errors!
    function shutdownFatalHandler()
    {
        $error = error_get_last();
        if ($error !== null) {
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
} else {
    // Handling max memory size issues only!
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error !== null) {
            if (preg_match('/Allowed memory size/im', $error['message'])) {
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
    });
}

// Running!
try {
    require_once __DIR__ . '/lib/Zend/Application.php';

    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true', true);
        header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS', true);
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, X-HTTP-Method-Override, Content-Type, Accept, Pragma, Set-Cookie', true);
        header('Access-Control-Max-Age: 86400', true);
    }

    // Initializing the application!
    $ini = APPLICATION_PATH . '/configs/app.ini';
    if (!is_readable(APPLICATION_PATH . '/configs/app.ini')) {
        $ini = APPLICATION_PATH . '/configs/app.sample.ini';
    }

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

    $application->bootstrap()->run();
} catch (\Exception $e) {
    ob_clean();
    http_response_code(400);

    $payload = [
        'error' => true,
        'message' => ($e->getPrevious()) ?
            $e->getPrevious()->getMessage() : $e->getMessage(),
    ];

    exit('<pre>' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

// Revert umask!
umask($oldUmask);
