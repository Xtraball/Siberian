<?php
/**
 *
 * Schema definition for 'push_certificate'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['push_certificate'] = [
    'certificate_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'type' => [
        'type' => 'varchar(30)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'path' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];