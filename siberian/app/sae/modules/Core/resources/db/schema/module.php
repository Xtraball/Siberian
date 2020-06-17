<?php
/**
 *
 * Schema definition for 'module'
 *
 * Last update: 2018-09-17
 *
 */
$schemas = $schemas ?? [];
$schemas['module'] = [
    'module_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'code' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'version' => [
        'type' => 'varchar(30)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'use_license' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'can_uninstall' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_enabled' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'type' => [
        'type' => 'varchar(512)',
        'default' => 'module',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
