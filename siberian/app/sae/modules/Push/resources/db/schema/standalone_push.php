<?php
/**
 *
 * Schema definition for "standalone_push"
 *
 * Last update: 2019-08-19
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["standalone_push"] = [
    "push_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
        "null" => true,
    ],
    "app_id" => [
        "type" => "int(11) unsigned",
        "null" => true,
    ],
    "tokens" => [
        "type" => "longtext",
    ],
    "request_id" => [
        "type" => "int(11) unsigned",
        "null" => true,
    ],
    "title" => [
        "type" => "longtext",
        "null" => true,
    ],
    "message" => [
        "type" => "longtext",
        "null" => true,
    ],
    "cover" => [
        "type" => "varchar(1024)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "action_value" => [
        "type" => "longtext",
        "null" => true,
    ],
    "sent_at" => [
        "type" => "int(11) unsigned",
        "null" => true,
    ],
    "status" => [
        "type" => "varchar(16)",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "message_json" => [
        "type" => "longtext",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
];
