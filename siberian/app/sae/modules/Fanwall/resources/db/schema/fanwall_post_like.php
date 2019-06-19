<?php
/**
 *
 * Schema definition for "fanwall_post_like"
 *
 * Last update: 2019-06-17
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["fanwall_post_like"] = [
    "like_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "post_id" => [
        "type" => "int(11) unsigned",
        "foreign_key" => [
            "table" => "fanwall_post",
            "column" => "post_id",
            "name" => "FPL_POSTID_FP_POSTID",
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
        "type" => "int(11) unsigned",
        "is_null" => true,
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
];
