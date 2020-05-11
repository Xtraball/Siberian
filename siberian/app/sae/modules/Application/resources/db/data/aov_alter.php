<?php

$alterQueries = [
    "ALTER TABLE `application_option` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;",
    "ALTER TABLE `application_option` CHANGE `code` `code` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;",
    "ALTER TABLE `application_option` CHANGE `position` `position` INT(11) UNSIGNED NOT NULL DEFAULT '0';",
    "ALTER TABLE `application` CHANGE `locale` `locale` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;",
    "ALTER TABLE `application` DROP INDEX `UNIQUE_KEY_SUBDOMAIN_DOMAIN`;",
    "ALTER TABLE `application` DROP INDEX `search_domain`;",
    "ALTER TABLE `application` DROP INDEX `search_key`;",
];

foreach ($alterQueries as $alterQuery) {
    try {
        $this->query($alterQuery);
    } catch (\Exception $e) {
        // Ok
    }
}
