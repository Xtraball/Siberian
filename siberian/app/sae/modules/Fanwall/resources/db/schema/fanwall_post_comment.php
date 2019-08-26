<?php
/**
 *
 * Schema definition for "fanwall_post_comment"
 *
 * Last update: 2019-06-17
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["fanwall_post_comment"] = [
    "comment_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "post_id" => [
        "type" => "int(11) unsigned",
        "foreign_key" => [
            "table" => "fanwall_post",
            "column" => "post_id",
            "name" => "FPC_POSTID_FP_POSTID",
            "on_update" => "CASCADE",
            "on_delete" => "CASCADE",
        ],
        "index" => [
            "key_name" => "KEY_COMMENT_ID",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "customer_id" => [
        "type" => "int(11)",
        "index" => [
            "key_name" => "KEY_CUSTOMER_ID",
            "index_type" => "BTREE",
            "is_null" => false,
            "is_unique" => false,
        ],
    ],
    "text" => [
        "type" => "varchar(2048)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "picture" => [
        "type" => "varchar(255)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "is_null" => true,
    ],
    "flag" => [
        "type" => "tinyint(1) unsigned",
        "default" => "0",
    ],
    "is_visible" => [
        "type" => "tinyint(1)",
        "default" => "1",
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
    "date" => [
        "type" => "int(11) unsigned",
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