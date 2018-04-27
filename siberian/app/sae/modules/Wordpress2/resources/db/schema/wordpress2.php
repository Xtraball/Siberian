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
        'index' => [
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'title' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'subtitle' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
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
    'sort_type' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'date',
    ],
    'sort_order' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'desc',
    ],
    'show_cover' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'show_title' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'strip_shortcode' => [
        'type' => 'tinyint(1)',
        'default' => '0',
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
