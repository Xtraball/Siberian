<?php
/**
 * Schema definition for 'fw_log'
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['fw_log'] = [
    'log_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'type' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'message' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'user_id' => [
        'type' => 'int(11)',
    ],
    'user_class' => [
        'type' => 'varchar(64)',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];