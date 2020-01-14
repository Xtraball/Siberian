<?php

$queries = [
    "ALTER TABLE `fanwall_post` CHANGE `image` `image` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;"
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Silent!
    }
}