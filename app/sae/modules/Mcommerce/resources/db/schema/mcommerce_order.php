<?php
/**
 *
 * Schema definition for 'mcommerce_order'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_order'] = array(
    'order_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'mcommerce_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_order_ibfk_1',
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
    'cart_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_cart',
            'column' => 'cart_id',
            'name' => 'mcommerce_order_ibfk_3',
            'on_update' => 'CASCADE',
            'on_delete' => 'RESTRICT',
        ),
        'index' => array(
            'key_name' => 'KEY_CART_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'store_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_store',
            'column' => 'store_id',
            'name' => 'mcommerce_order_ibfk_2',
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
    'customer_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'number' => array(
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'status_id' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'payment_method_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'payment_method' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'delivery_method_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'delivery_method' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_firstname' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_lastname' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_email' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
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
    'customer_phone' => array(
        'type' => 'varchar(15)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'subtotal_excl_tax' => array(
        'type' => 'decimal(12,2)',
    ),
    'total_excl_tax' => array(
        'type' => 'decimal(12,2)',
    ),
    'total_tax' => array(
        'type' => 'decimal(12,2)',
    ),
    'delivery_cost' => array(
        'type' => 'decimal(12,2) unsigned',
    ),
    'total' => array(
        'type' => 'decimal(12,2)',
    ),
    'paid_amount' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    // TG-459
    'notes' => array(
        'type' => 'text',
        'is_null' => true,
    ),
    'discount_code' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'paid_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    // pourboire
    'tip' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
        'default' => 0
    ),
);