<?php
/**
 *
 * Schema definition for 'push_messages'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['push_messages'] = array(
    'message_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'message_global_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'target_devices' => array(
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'all',
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'push_messages_ibfk_1',
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
    'type_id' => array(
        'type' => 'int(2)',
        'default' => '1',
    ),
    'title' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'text' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'base64' => array(
      'type' => 'tinyint(1)',
      'default' => '0'      
    ),
    'base_url' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'cover' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'with_image' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '1',
    ),
    'custom_image' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'action_value' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'latitude' => array(
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ),
    'longitude' => array(
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ),
    'radius' => array(
        'type' => 'decimal(7,2)',
        'is_null' => true,
    ),
    'send_to_all' => array(
        'type' => 'tinyint(1)',
    ),
    'send_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'send_until' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'delivered_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_PUSH_MESSAGES_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'status' => array(
        'type' => 'enum(\'queued\',\'delivered\',\'sending\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ),
    'error_text' => array(
        'type' => 'text',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);
