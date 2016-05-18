<?php
/**
 * SiberianCMS
 *
 * @version 4.1.0
 * @author Xtraball SAS <dev@xtraball.com>
 *
 */

$env = "production";

set_time_limit(120);
ini_set('max_execution_time', 120);
umask(0);

setlocale(LC_MONETARY, 'en_US');

/** @deprecated from 4.1.0, as we support only linux/unix based servers, this isn't necessary */
defined('DS')
    || define('DS', DIRECTORY_SEPARATOR);

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)."/app"));

/** Defining ENV globally */
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', $env);

/** Sourcing default libs */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH."/../lib"),
)));

require_once "Zend/Application.php";

/** Initializing the application */
$ini = is_readable(APPLICATION_PATH."/configs/app.ini") ? APPLICATION_PATH."/configs/app.ini" : APPLICATION_PATH."/configs/app.sample.ini";
$application = new Zend_Application(
    APPLICATION_ENV,
    $ini
);

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);

session_cache_limiter(false);

/** Running */
$application->bootstrap()->run();
