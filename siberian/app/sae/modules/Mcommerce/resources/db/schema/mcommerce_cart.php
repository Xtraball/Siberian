<?php
/**
 *
 * Schema definition for 'mcommerce_cart'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_cart'] = [
    'cart_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'mcommerce_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_cart_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_MCOMMERCE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'delivery_method_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'payment_method_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'customer_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'customer_firstname' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_lastname' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_email' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_phone' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_birthday' => [
        'type' => 'date',
        'is_null' => true,
    ],
    'customer_street' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_postcode' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_city' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_latitude' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_longitude' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'store_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_store',
            'column' => 'store_id',
            'name' => 'mcommerce_cart_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_STORE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'subtotal_excl_tax' => [
        'type' => 'double',
    ],
    'delivery_cost' => [
        'type' => 'double',
    ],
    'delivery_tax_rate' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'total_excl_tax' => [
        'type' => 'double',
    ],
    'total_tax' => [
        'type' => 'double',
    ],
    'total' => [
        'type' => 'double',
    ],
    'paid_amount' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'discount_code' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'tip' => [
        'type' => 'double',
        'is_null' => true,
        'default' => 0
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];