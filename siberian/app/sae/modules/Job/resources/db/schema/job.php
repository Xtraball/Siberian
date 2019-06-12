<?php
/**
 *
 * Schema definition for "mcommerce"
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["job"] = [
    "job_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
    ],
    "display_search" => [
        "type" => "tinyint(1) unsigned",
        "default" => "1",
    ],
    "display_place_icon" => [
        "type" => "tinyint(1) unsigned",
        "default" => "0",
    ],
    "display_income" => [
        "type" => "tinyint(1) unsigned",
        "default" => "1",
    ],
    "currency" => [
        "type" => "varchar(10)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "USD",
    ],
    "card_design" => [
        "type" => "varchar(16)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "list",
    ],
    "display_contact" => [
        "type" => "enum(\"hidden\",\"contactform\",\"email\",\"both\")",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "contactform",
    ],
    "title_company" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "Company",
    ],
    "title_place" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "Company",
    ],
    "default_radius" => [
        "type" => "tinyint(2)",
        "default" => 4,
    ],
    "distance_unit" => [
        "type" => "varchar(4)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "km",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];