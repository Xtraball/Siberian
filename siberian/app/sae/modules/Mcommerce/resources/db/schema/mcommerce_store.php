<?php
/**
 *
 * Schema definition for 'mcommerce_store'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_store'] = [
    'store_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'mcommerce_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_store_ibfk_1',
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
    'name' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'email' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'street' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'postcode' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'city' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'country' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'latitude' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'longitude' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'phone' => [
        'type' => 'varchar(25)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'delivery_fees' => [
        'type' => 'double',
        'default' => '0.00',
    ],
    'min_amount' => [
        'type' => 'double',
        'default' => '0.00',
    ],
    'min_amount_free_delivery' => [
        'type' => 'double',
        'default' => '0.00',
    ],
    'clients_calculate_change' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
    ],
    'delivery_area' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'delivery_time' => [
        'type' => 'double',
        'is_null' => true,
    ],
    'opening_hours' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_visible' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'currency_code' => [
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];