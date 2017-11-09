<?php
/**
 *
 * Schema definition for 'loyalty_card_customer_log'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['loyalty_card_customer_log'] = array(
    'log_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
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
    'card_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'loyalty_card',
            'column' => 'card_id',
            'name' => 'loyalty_card_customer_log_ibfk_1',
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
    'password_id' => array(
        'type' => 'int(11)',
        'index' => array(
            'key_name' => 'KEY_PASSWORD_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'number_of_points' => array(
        'type' => 'smallint(5) unsigned',
        'default' => '1',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
);