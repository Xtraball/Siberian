<?php
/**
 *
 * Schema definition for 'booking'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["booking"] = [
    "booking_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
    ],
    "datepicker" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "single",
    ],
    "cover" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "description" => [
        "type" => "text",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
];