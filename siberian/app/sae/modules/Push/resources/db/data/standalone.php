<?php
/**
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.6
 */

use Siberian\Feature;

$module = (new Installer_Model_Installer_Module())
    ->prepare("Push");

// Install the cron job
Feature::installCronjob(
    p__("push", "Standalone push scheduler."),
    "Push_Model_StandalonePush::sendScheduled",
    -1,
    -1,
    -1,
    -1,
    -1,
    true,
    100,
    false,
    $module->getId()
);

$alters = [
    "ALTER TABLE `standalone_push` CHANGE `value_id` `value_id` INT(11) UNSIGNED NULL DEFAULT NULL;",
    "ALTER TABLE `standalone_push` CHANGE `app_id` `app_id` INT(11) UNSIGNED NULL DEFAULT NULL;",
    "ALTER TABLE `standalone_push` CHANGE `title` `title` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;",
    "ALTER TABLE `standalone_push` CHANGE `message` `message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;",
    "ALTER TABLE `standalone_push` CHANGE `action_value` `action_value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;",
    "ALTER TABLE `standalone_push` CHANGE `send_at` `send_at` INT(11) UNSIGNED NULL DEFAULT NULL;",
];