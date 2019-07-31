<?php
/**
 *
 * Schema definition for "fanwall_post"
 *
 * Last update: 2019-06-17
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["fanwall_post"] = [
    "post_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
        "foreign_key" => [
            "table" => "application_option_value",
            "column" => "value_id",
            "name" => "FP_VID_AOV_VID",
            "on_update" => "CASCADE",
            "on_delete" => "CASCADE",
        ],
        "index" => [
            "key_name" => "KEY_VALUE_ID",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "customer_id" => [
        "type" => "int(11) unsigned",
        "is_null" => true,
        "foreign_key" => [
            "table" => "customer",
            "column" => "customer_id",
            "name" => "FP_CID_CUSTOMER_CID",
            "on_update" => "CASCADE",
            "on_delete" => "CASCADE",
        ],
        "index" => [
            "key_name" => "FK_CUSTOMER_ID",
            "index_type" => "BTREE",
            "is_null" => true,
            "is_unique" => false,
        ],
    ],
    "title" => [
        "type" => "varchar(100)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "subtitle" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "text" => [
        "type" => "text",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "image" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "default" => "",
    ],
    "date" => [
        "type" => "int(11) unsigned",
    ],
    "is_visible" => [
        "type" => "int(11)",
        "is_null" => true,
        "default" => "1",
    ],
    "latitude" => [
        "type" => "decimal(11,8)",
        "is_null" => true,
    ],
    "longitude" => [
        "type" => "decimal(11,8)",
        "is_null" => true,
    ],
    "location_short" => [
        "type" => "varchar(255)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "flag" => [
        "type" => "tinyint(1) unsigned",
        "default" => "0",
    ],
    "sticky" => [
        "type" => "tinyint(1) unsigned",
        "default" => "0",
    ],
    "is_reported" => [
        "type" => "tinyint(1)",
        "default" => "0",
    ],
    "report_reasons" => [
        "type" => "longtext",
    ],
    "report_token" => [
        "type" => "varchar(255)",
        "default" => "",
    ],
    "customer_ip" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "is_null" => true,
    ],
    "user_agent" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "is_null" => true,
    ],
    "history" => [
        "type" => "longtext",
        "is_null" => true,
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];