<?php
/**
 *
 * Schema definition for 'mcommerce_order_line'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_order_line'] = [
    'line_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'order_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_order',
            'column' => 'order_id',
            'name' => 'mcommerce_order_line_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_ORDER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'cart_line_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_cart_line',
            'column' => 'line_id',
            'name' => 'mcommerce_order_line_ibfk_3',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_LINE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'ref' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'name' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'base_price' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'base_price_incl_tax' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'price' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'price_incl_tax' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'qty' => [
        'type' => 'double',
        'default' => '1.00',
    ],
    'total' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'total_incl_tax' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'choices' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'options' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'format' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'tax_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_tax',
            'column' => 'tax_id',
            'name' => 'mcommerce_order_line_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'RESTRICT',
        ],
        'index' => [
            'key_name' => 'KEY_TAX_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'tax_rate' => [
        'type' => 'double',
    ],
];