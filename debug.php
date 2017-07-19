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

if(!isset($_config["debug"]) || !$_config["debug"]) {
    die;
}

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

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);
Zend_Registry::set('_config', $_config);

session_cache_limiter(false);

/** Running */
$application->bootstrap();

Siberian_Debug::handle();