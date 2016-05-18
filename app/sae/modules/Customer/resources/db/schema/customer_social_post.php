<?php
/**
 *
 * Schema definition for 'customer_social_post'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['customer_social_post'] = array(
    'id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'customer_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'customer',
            'column' => 'customer_id',
            'name' => 'customer_social_post_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'customer_message' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'message_type' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'points' => array(
        'type' => 'tinyint(2)',
        'default' => '1',
    ),
);