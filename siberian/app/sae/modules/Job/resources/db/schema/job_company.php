<?php

$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["job_company"] = [
    "company_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "job_id" => [
        "type" => "int(11) unsigned",
        "index" => [
            "key_name" => "company_job_id",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "name" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "description" => [
        "type" => "text",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "website" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "location" => [
        "type" => "varchar(512)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "latitude" => [
        "type" => "varchar(32)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "longitude" => [
        "type" => "varchar(32)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "employee_count" => [
        "type" => "varchar(128)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "email" => [
        "type" => "varchar(64)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "administrators" => [
        "type" => "varchar(1024)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "logo" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "header" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "display_contact" => [
        "type" => "enum(\"global\",\"hidden\",\"contactform\",\"email\",\"both\")",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "global",
    ],
    "views" => [
        "type" => "int(11) unsigned",
        "default" => "0",
    ],
    "is_active" => [
        "type" => "tinyint(1) unsigned",
        "default" => "0",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];