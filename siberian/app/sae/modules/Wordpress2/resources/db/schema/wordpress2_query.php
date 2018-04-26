<?php
/**
 *
 * Schema definition for 'wordpress2_category'
 *
 * Last update: 2018-02-09
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['wordpress2_query'] = [
    'query_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'KEY_QUERY_ID',
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
    'show_cover' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'show_title' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'picture' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'thumbnail' => [
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
    'query' => [
        'type' => 'longtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'position' => [
        'type' => 'int(11) unsigned',
        'default' => '1',
    ],
    'is_published' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
];
