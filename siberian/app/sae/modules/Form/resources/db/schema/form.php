<?php
/**
 *
 * Schema definition for "form"
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["form"] = [
    "form_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
    ],
    "email" => [
        "type" => "text",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "design" => [
        "type" => "varchar(16)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "list",
    ],
    "date_format" => [
        "type" => "varchar(32)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "MM/DD/YYYY HH:mm",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];