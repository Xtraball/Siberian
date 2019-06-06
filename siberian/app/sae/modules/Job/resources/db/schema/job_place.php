<?php
/**
 *
 * Schema definition for "mcommerce"
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["job_place"] = [
    "place_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "company_id" => [
        "type" => "int(11) unsigned",
        "index" => [
            "key_name" => "company_id",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "category_id" => [
        "type" => "int(11) unsigned",
        "index" => [
            "key_name" => "category_id",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "contract_type" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "name" => [
        "type" => "varchar(50)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "description" => [
        "type" => "text",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "email" => [
        "type" => "varchar(255)",
        "is_null" => true,
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
    "income_from" => [
        "type" => "varchar(128)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "income_to" => [
        "type" => "varchar(128)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "icon" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "banner" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "keywords" => [
        "type" => "text",
        "charset" => "utf8",
        "is_null" => true,
        "collation" => "utf8_unicode_ci",
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