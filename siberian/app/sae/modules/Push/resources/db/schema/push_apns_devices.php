<?php
/**
 *
 * Schema definition for 'push_apns_devices'
 *
 * Last update: 2020-04-12
 *
 */
$schemas = $schemas ?? [];
$schemas['push_apns_devices'] = [
    'device_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'push_apns_devices_ibfk_1',
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
    'customer_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => [
            'table' => 'customer',
            'column' => 'customer_id',
            'name' => 'FK_PUSH_APNS_DEVICES_CUSTOMER_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'customer_id',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ],
    ],
    'app_name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => [
            'key_name' => 'UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ],
    ],
    'app_version' => [
        'type' => 'varchar(25)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => [
            'key_name' => 'UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => true,
        ],
    ],
    'device_uid' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => [
            'key_name' => 'UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ],
    ],
    'last_known_latitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'last_known_longitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'device_token' => [
        'type' => 'char(64)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => [
            'key_name' => 'KEY_DEVICE_TOKEN',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'device_name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'device_model' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'device_version' => [
        'type' => 'varchar(25)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'push_badge' => [
        'type' => 'enum(\'disabled\',\'enabled\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled',
    ],
    'push_alert' => [
        'type' => 'enum(\'disabled\',\'enabled\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled',
    ],
    'push_sound' => [
        'type' => 'enum(\'disabled\',\'enabled\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled',
    ],
    'status' => [
        'type' => 'enum(\'active\',\'uninstalled\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'active',
        'index' => [
            'key_name' => 'KEY_STATUS',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'error_count' => [
        'type' => 'tinyint(1) unsigned',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'timestamp',
        'default' => 'CURRENT_TIMESTAMP',
    ],
];
