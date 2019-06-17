<?php
/**
 *
 * Schema definition for "mcommerce"
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["job_place_contact"] = [
    "place_contact_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "place_id" => [
        "type" => "int(11) unsigned",
        "index" => [
            "key_name" => "place_id_id",
            "index_type" => "BTREE",
            "is_null" => true,
            "is_unique" => false,
        ],
    ],
    "customer_id" => [
        "type" => "int(11) unsigned",
        "index" => [
            "key_name" => "customer_id",
            "index_type" => "BTREE",
            "is_null" => true,
            "is_unique" => false,
        ],
        "is_null" => true,
    ],
    "fullname" => [
        "type" => "varchar(255)",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "email" => [
        "type" => "varchar(255)",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "phone" => [
        "type" => "varchar(255)",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "address" => [
        "type" => "varchar(255)",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "message" => [
        "type" => "text",
        "is_null" => false,
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
    ],
    "from_admin" => [
        "type" => "int(11) unsigned",
        "default" => "0",
    ],
    "is_new" => [
        "type" => "int(11) unsigned",
        "default" => "1",
    ],
    "created_at" => [
        "type" => "datetime",
    ],
    "updated_at" => [
        "type" => "datetime",
    ],
];