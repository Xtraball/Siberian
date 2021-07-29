<?php

$queries = [
    "ALTER TABLE `customer` CHANGE `birthdate` `birthdate` BIGINT(20) NOT NULL;"
];

foreach ($queries as $query) {
    try {
        $this->query($query);
    } catch (\Exception $e) {
        // Plouf
    }
}