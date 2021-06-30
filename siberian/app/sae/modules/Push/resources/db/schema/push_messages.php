<?php
/**
 *
 * Schema definition for 'push_messages'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['push_messages'] = [
    'message_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'message_global_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'target_devices' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'all',
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'push_messages_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'FK_APPLICATION_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'type_id' => [
        'type' => 'int(2)',
        'default' => '1',
    ],
    'title' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'text' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'base64' => [
      'type' => 'tinyint(1)',
      'default' => '0'
    ],
    'base_url' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'cover' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'with_image' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '1',
    ],
    'custom_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'action_value' => [
        'type' => 'varchar(255)',
        'is_null' => true,
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
    'send_to_all' => [
        'type' => 'tinyint(1)',
    ],
    'send_at' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
    'send_until' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
    'delivered_at' => [
        'type' => 'datetime',
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
            'name' => 'FK_PUSH_MESSAGES_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'status' => [
        'type' => 'enum(\'queued\',\'delivered\',\'sending\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ],
    'error_text' => [
        'type' => 'text',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
