<?php
/**
 *
 * Schema definition for 'loyalty_card_customer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['loyalty_card_customer'] = [
    'customer_card_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'card_id' => [
        'type' => 'int(11)',
        'foreign_key' => [
            'table' => 'loyalty_card',
            'column' => 'card_id',
            'name' => 'loyalty_card_customer_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_CARD_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'KEY_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'number_of_points' => [
        'type' => 'smallint(5)',
    ],
    'is_used' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'number_of_error' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'validate_by' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
    'used_at' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
    'last_error' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
];