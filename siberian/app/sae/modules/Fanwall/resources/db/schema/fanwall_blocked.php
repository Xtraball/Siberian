<?php
/**
 *
 * Schema definition for "fanwall_post"
 *
 * Last update: 2019-06-17
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["fanwall_blocked"] = [
    "blocked_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
        "foreign_key" => [
            "table" => "application_option_value",
            "column" => "value_id",
            "name" => "FBU_VID_AOV_VID",
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
            "name" => "FBU_CID_CUSTOMER_CID",
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
    "blocked_users" => [
        "type" => "longtext",
    ],
    "blocked_posts" => [
        "type" => "longtext",
    ],
    "blocked_comments" => [
        "type" => "longtext",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];