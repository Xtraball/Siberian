<?php

/**
 * SiberianCMS
 *
 * @version 4.1.0
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @development fast-env switch
 *
 */
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
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)."/app"));

/** Defining ENV globally */
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', $_config["environment"]);

/** Sourcing default libs */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH."/../lib"),
)));

require_once "Zend/Application.php";

/** Initializing the application */
$ini = is_readable(APPLICATION_PATH."/configs/app.ini") ? APPLICATION_PATH."/configs/app.ini" : APPLICATION_PATH."/configs/app.sample.ini";
$application = new Zend_Application(
    $_config["environment"],
    array(
        "config" => array(
            $ini,
            APPLICATION_PATH."/configs/resources.cachemanager.ini",
        ),
    )
);

# PHPINFO
if($_config["environment"] == "development" && isset($_GET["phpi"])) {
    phpinfo();
    die;
}

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);
Zend_Registry::set('_config', $_config);

session_cache_limiter(false);

# Handle fatal errors
function sbfatal() {
    $error = error_get_last();
    if($error != null) {
        ob_clean();
        http_response_code(400);

        $error_msg = str_replace("\n", " - ", $error["message"]);
        $data = array(
            "error" => true,
            "message" => (APPLICATION_ENV == "development") ? "FATAL ERROR: {$error_msg}" : "An unknown fatal error occured, please try again.",
        );
        exit(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}

# register_shutdown_function("sbfatal");
# Handle fatal errors

/** Running */
$application->bootstrap()->run();
