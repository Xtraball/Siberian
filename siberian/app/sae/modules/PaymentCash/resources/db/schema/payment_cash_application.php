<?php
/**
 *
 * Schema definition for 'payment_cash_application'
 *
 * Last update: 2019-09-11
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['payment_cash_application'] = [
    'cash_application_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'PCSHAPP_KEY_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'is_enabled' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
