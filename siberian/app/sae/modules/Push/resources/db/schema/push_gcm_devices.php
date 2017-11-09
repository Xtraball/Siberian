<?php
/**
 *
 * Schema definition for 'push_gcm_devices'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['push_gcm_devices'] = array(
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
            'name' => 'push_gcm_devices_ibfk_1',
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
    ),
    'device_uid' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'registration_id' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'development' => array(
        'type' => 'enum(\'production\',\'sandbox\')',
        'charset' => 'latin1',
        'collation' => 'latin1_swedish_ci',
        'default' => 'production',
    ),
    'status' => array(
        'type' => 'enum(\'active\',\'uninstalled\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'active',
    ),
    'error_count' => array(
        'type' => 'tinyint(1) unsigned',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'timestamp',
        'default' => '0000-00-00 00:00:00',
    ),
);