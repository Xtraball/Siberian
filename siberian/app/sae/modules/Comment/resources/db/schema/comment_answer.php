<?php
/**
 *
 * Schema definition for 'comment_answer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['comment_answer'] = [
    'answer_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'comment_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'comment',
            'column' => 'comment_id',
            'name' => 'comment_answer_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_COMMENT_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'customer_id' => [
        'type' => 'int(11)',
        'index' => [
            'key_name' => 'KEY_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'text' => [
        'type' => 'varchar(2048)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'flag' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_visible' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
    'created_at_utc' => [
        'type' => 'bigint'
    ],
    'updated_at_utc' => [
        'type' => 'bigint'
    ]
];