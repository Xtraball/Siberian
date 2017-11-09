<?php
/**
 *
 * Schema definition for 'catalog_product'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_product'] = array(
    'product_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'category_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
        'index' => array(
            'key_name' => 'KEY_CATEGORY_ID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ),
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'catalog_product_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'mcommerce_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'index' => array(
            'key_name' => 'KEY_MCOMMERCE_ID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ),
    ),
    'tax_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'library_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'library_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'type' => array(
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'basic',
    ),
    'name' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'description' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'picture' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'price' => array(
        'type' => 'decimal(12,2)',
        'is_null' => true,
    ),
    'format_quantity' => array(
        'type' => 'tinyint(2)',
        'default' => '0',
    ),
    'conditions' => array(
        'type' => 'varchar(75)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_active' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'position' => array(
        'type' => 'smallint(5)',
        'default' => '0',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);