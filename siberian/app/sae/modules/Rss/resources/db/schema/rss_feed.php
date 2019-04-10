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
    "replace_title" => [
        "type" => "tinyint(1)",
        "default" => "0",
    ],
    "subtitle" => [
        "type" => "text",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "is_null" => true,
    ],
    "replace_subtitle" => [
        "type" => "tinyint(1)",
        "default" => "0",
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
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "is_null" => true,
    ],
    "replace_thumbnail" => [
        "type" => "tinyint(1)",
        "default" => "0",
    ],
    "version" => [
        "type" => "tinyint(1)",
        "default" => "1",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];