<?php
/**
 * SiberianCMS
 *
 * @version 4.1.0
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @note preparation for the incoming cron scheduler.
 *
 */
error_reporting(0);

if(version_compare(PHP_VERSION, '5.3.0') < 0) {
    die("PHP >=5.3 is required.\n");
}

require_once dirname(__FILE__)."/config.php";

if(
    (isset($_ENV["SHELL"]) && !empty($_ENV["SHELL"])) ||
    (isset($_SERVER["SHELL"]) && !empty($_SERVER["SHELL"])) ||
    php_sapi_name() == 'cli'
) {
    # Continue
} else {
    if(isset($_GET["cron_secret"]) && isset($_config["cron_secret"]) && ($_GET["cron_secret"] == $_config["cron_secret"])) {
        # Continue
    } else {
        die("You must run from CLI.\n");
    }
}


define('APPLICATION_PATH', realpath(dirname(__FILE__)."/app"));
define('APPLICATION_ENV', $_config["environment"]);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH."/../lib"),
)));

require_once "Zend/Application.php";

$application = new Zend_Application(
    $_config["environment"],
    array(
        "config" => APPLICATION_PATH."/configs/app.ini",
        "bootstrap" => array(
            "path" => APPLICATION_PATH."/BootstrapCron.php",
            "class" => "BootstrapCron",
        ),
    )
);

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);

/** Init bootstrap */
$application->bootstrap();

/** Run cron */
$cron = new Siberian_Cron();
$cron->triggerAll();
