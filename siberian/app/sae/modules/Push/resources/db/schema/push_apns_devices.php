<?php
/**
 *
 * Schema definition for 'push_apns_devices'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['push_apns_devices'] = array(
    'device_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'push_apns_devices_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_APPLICATION_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'app_name' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ),
    ),
    'app_version' => array(
        'type' => 'varchar(25)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => true,
        ),
    ),
    'device_uid' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ),
    ),
    'last_known_latitude' => array(
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ),
    'last_known_longitude' => array(
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ),
    'device_token' => array(
        'type' => 'char(64)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'KEY_DEVICE_TOKEN',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'device_name' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'device_model' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'device_version' => array(
        'type' => 'varchar(25)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'push_badge' => array(
        'type' => 'enum(\'disabled\',\'enabled\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled',
    ),
    'push_alert' => array(
        'type' => 'enum(\'disabled\',\'enabled\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled',
    ),
    'push_sound' => array(
        'type' => 'enum(\'disabled\',\'enabled\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled',
    ),
    'status' => array(
        'type' => 'enum(\'active\',\'uninstalled\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'active',
        'index' => array(
            'key_name' => 'KEY_STATUS',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'error_count' => array(
        'type' => 'tinyint(1) unsigned',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'timestamp',
        'default' => 'CURRENT_TIMESTAMP',
    ),
);