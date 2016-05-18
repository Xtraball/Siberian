<?php
/**
 *
 * Schema definition for 'catalog_product_format'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_product_format'] = array(
    'option_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'product_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'catalog_product',
            'column' => 'product_id',
            'name' => 'catalog_product_format_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_PRODUCT_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'title' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'price' => array(
        'type' => 'decimal(8,2)',
    ),
);