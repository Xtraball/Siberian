<?php
/**
 *
 * Schema definition for 'discount_customer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['discount_customer'] = [
    'discount_customer_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'discount_id' => [
        'type' => 'int(11)',
        'foreign_key' => [
            'table' => 'discount',
            'column' => 'discount_id',
            'name' => 'DISCOUNT_CUSTOMER_DID_DISCOUNT_DID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_PROMOTION_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'pos_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
    ],
    'is_used' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'number_of_error' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'last_error' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
];