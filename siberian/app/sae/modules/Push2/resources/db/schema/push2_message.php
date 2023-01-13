<?php
/**
 *
 * Schema definition for 'push2_message'
 *
 * Last update: 2023-01-14
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['push2_message'] = [
    'message_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'FK_PUSH2_MESSAGE_APP_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PUSH2_MESSAGE_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'title' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'subtitle' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'body' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'latitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'longitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'radius' => [
        'type' => 'decimal(7,2)',
        'is_null' => true,
    ],
    'is_silent' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_PUSH2_MESSAGES_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PUSH2_MESSAGES_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'status' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
