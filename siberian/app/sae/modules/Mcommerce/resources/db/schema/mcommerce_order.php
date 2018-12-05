<?php
/**
 *
 * Schema definition for 'mcommerce_order'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_order'] = [
    'order_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'mcommerce_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_order_ibfk_1',
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
    'cart_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_cart',
            'column' => 'cart_id',
            'name' => 'mcommerce_order_ibfk_3',
            'on_update' => 'CASCADE',
            'on_delete' => 'RESTRICT',
        ],
        'index' => [
            'key_name' => 'KEY_CART_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'store_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_store',
            'column' => 'store_id',
            'name' => 'mcommerce_order_ibfk_2',
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
    'customer_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'number' => [
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'status_id' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'payment_method_id' => [
        'type' => 'int(11) unsigned',
    ],
    'payment_method' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'delivery_method_id' => [
        'type' => 'int(11) unsigned',
    ],
    'delivery_method' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_firstname' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_lastname' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_email' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
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
    'customer_phone' => [
        'type' => 'varchar(15)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'subtotal_excl_tax' => [
        'type' => 'double',
    ],
    'total_excl_tax' => [
        'type' => 'double',
    ],
    'total_tax' => [
        'type' => 'double',
    ],
    'delivery_cost' => [
        'type' => 'double',
    ],
    'total' => [
        'type' => 'double',
    ],
    'paid_amount' => [
        'type' => 'double',
        'is_null' => true,
    ],
    // TG-459
    'notes' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'discount_code' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'paid_at' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
    // pourboire
    'tip' => [
        'type' => 'double',
        'is_null' => true,
        'default' => 0
    ],
];