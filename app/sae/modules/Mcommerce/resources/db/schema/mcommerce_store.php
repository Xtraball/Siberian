<?php
/**
 *
 * Schema definition for 'mcommerce_store'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_store'] = array(
    'store_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'mcommerce_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_store_ibfk_1',
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
    'name' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'email' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'street' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'postcode' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'city' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'country' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'latitude' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'longitude' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'phone' => array(
        'type' => 'varchar(25)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'delivery_fees' => array(
        'type' => 'decimal(12,2)',
        'default' => '0.00',
    ),
    'min_amount' => array(
        'type' => 'decimal(12,2)',
        'default' => '0.00',
    ),
    'min_amount_free_delivery' => array(
        'type' => 'decimal(12,2)',
        'default' => '0.00',
    ),
    'clients_calculate_change' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
    ),
    'delivery_area' => array(
        'type' => 'decimal(8,4)',
        'is_null' => true,
    ),
    'delivery_time' => array(
        'type' => 'decimal(4,2)',
        'is_null' => true,
    ),
    'opening_hours' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_visible' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'currency_code' => array(
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);