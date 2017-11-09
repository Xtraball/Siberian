<?php
/**
 *
 * Schema definition for 'mcommerce_cart'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_cart'] = array(
    'cart_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'mcommerce_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_cart_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_MCOMMERCE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'delivery_method_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'payment_method_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'customer_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'customer_firstname' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_lastname' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_email' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_phone' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_birthday' => array(
        'type' => 'date',
        'is_null' => true,
    ),
    'customer_street' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_postcode' => array(
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_city' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_latitude' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_longitude' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'store_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_store',
            'column' => 'store_id',
            'name' => 'mcommerce_cart_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_STORE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'subtotal_excl_tax' => array(
        'type' => 'decimal(12,2)',
    ),
    'delivery_cost' => array(
        'type' => 'decimal(12,2) unsigned',
    ),
    'delivery_tax_rate' => array(
        'type' => 'decimal(5,2)',
        'is_null' => true,
    ),
    'total_excl_tax' => array(
        'type' => 'decimal(12,2)',
    ),
    'total_tax' => array(
        'type' => 'decimal(12,2)',
    ),
    'total' => array(
        'type' => 'decimal(12,2)',
    ),
    'paid_amount' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'discount_code' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'tip' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
        'default' => 0
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);