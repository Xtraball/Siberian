<?php
/**
 *
 * Schema definition for "rss_feed"
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["rss_feed"] = [
    "feed_id" => [
        "type" => "int(11)",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
        "foreign_key" => [
            "table" => "application_option_value",
            "column" => "value_id",
            "name" => "rss_feed_ibfk_1",
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
    "title" => [
        "type" => "varchar(20)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "subtitle" => [
        "type" => "varchar(20)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "link" => [
        "type" => "varchar(100)",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "position" => [
        "type" => "tinyint(1) unsigned",
        "default" => "0",
    ],
    "picture" => [
        "type" => "tinyint(1)",
        "is_null" => true,
    ],
    "thumbnail" => [
        "type" => "text",
        "is_null" => true,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];