<?php
/**
 *
 * Schema definition for 'mcommerce_order_line'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_order_line'] = array(
    'line_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'order_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_order',
            'column' => 'order_id',
            'name' => 'mcommerce_order_line_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_ORDER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'cart_line_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_cart_line',
            'column' => 'line_id',
            'name' => 'mcommerce_order_line_ibfk_3',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_LINE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'ref' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'name' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'base_price' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'base_price_incl_tax' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'price' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'price_incl_tax' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'qty' => array(
        'type' => 'decimal(4,2)',
        'default' => '1.00',
    ),
    'total' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'total_incl_tax' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'choices' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'options' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'format' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'tax_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_tax',
            'column' => 'tax_id',
            'name' => 'mcommerce_order_line_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'RESTRICT',
        ),
        'index' => array(
            'key_name' => 'KEY_TAX_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'tax_rate' => array(
        'type' => 'decimal(5,2)',
    ),
);