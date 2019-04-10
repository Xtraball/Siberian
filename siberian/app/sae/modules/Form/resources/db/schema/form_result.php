<?php
/**
 *
 * Schema definition for "form_result"
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas["form_result"] = [
    "result_id" => [
        "type" => "int(11) unsigned",
        "auto_increment" => true,
        "primary" => true,
    ],
    "value_id" => [
        "type" => "int(11) unsigned",
    ],
    "customer_id" => [
        "type" => "int(11) unsigned",
        "is_null" => true,
    ],
    "payload" => [
        "type" => "longtext",
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