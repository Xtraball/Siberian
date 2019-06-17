<?php
/**
 *
 * Schema definition for "mcommerce"
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["job_category"] = [
    "category_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "job_id" => [
        "type" => "int(11) unsigned",
        "index" => [
            "key_name" => "category_job_id",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "name" => [
        "type" => "varchar(50)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "description" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "keywords" => [
        "type" => "varchar(512)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "icon" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
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