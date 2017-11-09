<?php
/**
 *
 * Schema definition for 'push_delivered_message'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['push_delivered_message'] = array(
    'deliver_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'device_id' => array(
        'type' => 'int(11)',
        'index' => array(
            'key_name' => 'KEY_DEVICE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'device_uid' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'device_type' => array(
        'type' => 'tinyint(1)',
    ),
    'message_id' => array(
        'type' => 'int(11)',
    ),
    'status' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'is_read' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'is_displayed' => array(
        'type' => 'int(11)',
        'default' => '0',
    ),
    'delivered_at' => array(
        'type' => 'datetime',
    ),
);