<?php
/**
 *
 * Schema definition for 'push_gcm_devices'
 *
 * Last update: 2020-04-12
 *
 */
$schemas = $schemas ?? [];
$schemas['push_gcm_devices'] = [
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
            'name' => 'push_gcm_devices_ibfk_1',
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
            'name' => 'FK_PUSH_GCM_DEVICES_CUSTOMER_ID',
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
    ],
    'device_uid' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'registration_id' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'provider' => [
        'type' => 'enum(\'gcm\',\'fcm\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'gcm',
    ],
    'development' => [
        'type' => 'enum(\'production\',\'sandbox\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'production',
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
    ],
    'error_count' => [
        'type' => 'tinyint(1) unsigned',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'timestamp',
        'default' => '0000-00-00 00:00:00',
    ],
];
