<?php
/**
 * SiberianCMS
 *
 * @version 4.1.0
 * @author Xtraball SAS <dev@xtraball.com>
 *
 * @configuration
 *
 */

$_config = array();
$_config["environment"] = "development";

/** local headers */
if(!empty($_SERVER["HTTP_ORIGIN"])) {
    header("Access-Control-Allow-Origin: {$_SERVER["HTTP_ORIGIN"]}");
    header("Access-Control-Allow-Credentials: true", true);
    header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS", true);
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, X-HTTP-Method-Override, Content-Type, Accept, Pragma, Set-Cookie", true);
    header("Access-Control-Max-Age: 86400", true);
}