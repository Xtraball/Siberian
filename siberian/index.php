<?php

/**
 * Siberian
 *
 * @version 5.0.7
 * @author Xtraball SAS <dev@xtraball.com>
 */

if (array_key_exists('REQUEST_METHOD', $_SERVER) &&
    ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true', true);
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS', true);
    header('Access-Control-Allow-Headers: Origin, Xsb-Auth, X-Requested-With, X-HTTP-Method-Override, Content-Type, Accept, Pragma, Set-Cookie', true);
    header('Access-Control-Max-Age: 86400', true);
    http_response_code(200);
    echo '{"success": true}';
    die;
}

require_once './lib/System/polyfills.php';

global $_config;

$here = __DIR__;
putenv("TMP={$here}/var/tmp");

$oldUmask = umask(003);

if (!file_exists('./config.php')) {
    copy('./config.sample.php', './config.php');
}

require_once './lib/System/shutdown.php';

if (!file_exists('./lib/Siberian/Version.php')) {
    copy('./lib/Siberian/Version.sample.php', './lib/Siberian/Version.php');
}

require_once './config.php';

/** HTTP to HTTPS redirection, while ensuring .well-known for let's encrypt is still working */
if (isset($_config['redirect_https']) &&
    $_SERVER['REQUEST_SCHEME'] === 'http' &&
    $_config['redirect_https'] === true
) {
    header('Status: 301 Moved Permanently', false, 301);
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

set_time_limit(300);
ini_set('max_execution_time', 300);
umask(0);

setlocale(LC_MONETARY, 'en_US');

require_once  './lib/System/defines.php';

// Sourcing default libs!
set_include_path(implode_polyfill(PATH_SEPARATOR, [
    dirname(APPLICATION_PATH) . '/lib',
]));

/**
 * @debug
 */
function dbg()
{
    $args = func_get_args();
    foreach ($args as $arg) {
        ob_start();
        print_r($arg);
        $content = ob_get_clean();

        file_put_contents(
            __DIR__ . '/var/tmp/debug.log',
            date('d/m/Y H:i:s') . ': ' . $content . PHP_EOL,
            FILE_APPEND);
    }
}

// Running!
try {
    require_once __DIR__ . '/lib/Zend/Application.php';

    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Credentials: true', true);
        header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS', true);
        header('Access-Control-Allow-Headers: Origin, Xsb-Auth, X-Requested-With, X-HTTP-Method-Override, Content-Type, Accept, Pragma, Set-Cookie', true);
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

    if (isset($_config['with_trace']) && $_config['with_trace'] === true) {
        $payload['trace'] = ($e->getPrevious()) ?
            $e->getPrevious()->getTrace() : $e->getTrace();
    }

    exit(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

// Revert umask!
umask($oldUmask);
