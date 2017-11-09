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
require_once dirname(__FILE__)."/config.php";

# Test purpose only
if(isset($argv) && isset($argv[1]) && ($argv[1] == "test")) {
    die("OK");
}

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

define('CRON', true);
define('APPLICATION_PATH', realpath(dirname(__FILE__)."/app"));
define('APPLICATION_ENV', $_config["environment"]);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH."/../lib"),
)));

require_once "Zend/Application.php";

$application = new Zend_Application(
    $_config["environment"],
    array(
        "config" => array(
            APPLICATION_PATH."/configs/app.ini",
            APPLICATION_PATH."/configs/resources.cachemanager.ini",
        ),
        "bootstrap" => array(
            "path" => APPLICATION_PATH."/BootstrapCron.php",
            "class" => "BootstrapCron",
        ),
    )
);

$config = new Zend_Config($application->getOptions(), true);
Zend_Registry::set('config', $config);
Zend_Registry::set('_config', $_config);

/** Init bootstrap */
$application->bootstrap();

/** Run cron */
$start = time();
$interval = System_Model_Config::getValueFor("cron_interval");
$interval = (($interval >= 10) && ($interval < 51)) ? $interval : false;

defined("DESIGN_CODE") || define("DESIGN_CODE", design_code());

/** Creating an Alert in the Backoffice rather than killing the process */
if(version_compare(PHP_VERSION, '5.6.0') < 0) {
    $description = "PHP version >= 5.6.0 is required for the cron scheduler to run correctly, your php-cli version is " . PHP_VERSION . ".";

    $notification = new Backoffice_Model_Notification();
    $notification
        ->setTitle(__("Alert: PHP version >= 5.6.0 is required for the cron scheduler to run correctly"))
        ->setDescription(__($description))
        ->setSource("cron")
        ->setType("alert")
        ->setIsHighPriority(1)
        ->setObjectType("cron_scheduler")
        ->setObjectId("42")
        ->save();

    die("PHP >=5.6 is required.\n");
}

$cron = new Siberian_Cron();
if(isset($argv) && isset($argv[1]) && ($argv[1] == 'runcommand')) {
    if(isset($argv[2])) {
        $cron->runTaskByCommand($argv[2]);
        die('Execution done.' . PHP_EOL);
    } else {
        die('Missing command name.' . PHP_EOL);
    }
} else {
    $cron->triggerAll();
}

/** Highly experimental, may increase server load. */
if($interval !== false) {
    $new_time = time() - $start;
    $loop = 0;
    while($new_time < 55) {
        sleep($interval);
        $cron->log("Interval repeat ".++$loop);
        $cron->triggerAll();
        $new_time = time() - $start;
    }
}

