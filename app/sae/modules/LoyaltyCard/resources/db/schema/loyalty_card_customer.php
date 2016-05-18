<?php
/**
 *
 * Schema definition for 'loyalty_card_customer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['loyalty_card_customer'] = array(
    'customer_card_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'card_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'loyalty_card',
            'column' => 'card_id',
            'name' => 'loyalty_card_customer_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_CARD_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'customer_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'KEY_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'number_of_points' => array(
        'type' => 'smallint(5)',
    ),
    'is_used' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'number_of_error' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'validate_by' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
    'used_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'last_error' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
);