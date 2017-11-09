<?php
/**
 *
 * Schema definition for 'promotion_customer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['promotion_customer'] = array(
    'promotion_customer_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'promotion_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'promotion',
            'column' => 'promotion_id',
            'name' => 'promotion_customer_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_PROMOTION_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'pos_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'customer_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'is_used' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'number_of_error' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'last_error' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
);