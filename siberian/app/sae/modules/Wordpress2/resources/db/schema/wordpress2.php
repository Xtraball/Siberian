<?php
/**
 *
 * Schema definition for 'wordpress2'
 *
 * Last update: 2018-02-09
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['wordpress2'] = [
    'wordpress2_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'wordpress2_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'url' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'login' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'password' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'picture' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'show_cover' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'group_queries' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'card_design' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'cache_lifetime' => [
        'type' => 'varchar(12)',
        'default' => '3600',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
