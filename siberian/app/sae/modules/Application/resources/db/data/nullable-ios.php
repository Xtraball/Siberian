<?php

// Update 4.15.15+

$columns = [
    "itunes_login",
    "itunes_password",
    "itunes_original_login",
];

foreach ($columns as $column) {
    try {
        $this->query("ALTER TABLE `application_ios_autobuild_information` CHANGE `{$column}` `{$column}` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    } catch (\Exception $e) {
        //
    }
}