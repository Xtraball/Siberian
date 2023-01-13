<?php
/**
 *
 * Schema definition for 'push_delivered_message'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['push_delivered_message'] = [
    'deliver_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'device_id' => [
        'type' => 'int(11)',
        'index' => [
            'key_name' => 'KEY_DEVICE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'device_uid' => [
        'type' => 'varchar(255)',
        'index' => [
            'key_name' => 'push_delivered_message_device_uid_index',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'device_type' => [
        'type' => 'tinyint(1)',
    ],
    'message_id' => [
        'type' => 'int(11)',
    ],
    'status' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'is_read' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'is_displayed' => [
        'type' => 'int(11)',
        'default' => '0',
    ],
    'delivered_at' => [
        'type' => 'datetime',
    ],
];