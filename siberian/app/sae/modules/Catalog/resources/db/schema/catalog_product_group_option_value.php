<?php
/**
 *
 * Schema definition for 'catalog_product_group_option_value'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_product_group_option_value'] = array(
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'group_value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'catalog_product_group_value',
            'column' => 'value_id',
            'name' => 'catalog_product_group_option_value_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_CATALOG_PRODUCT_GROUP_GROUP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'option_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'catalog_product_group_option',
            'column' => 'option_id',
            'name' => 'catalog_product_group_option_value_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_CATALOG_PRODUCT_GROUP_OPTION_OPTION_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'price' => array(
        'type' => 'decimal(12,2)',
        'default' => '0.00',
    ),
    'qty_min' => array(
        'type' => 'smallint(5)',
        'default' => '0',
    ),
    'qty_max' => array(
        'type' => 'smallint(5)',
        'is_null' => true,
    ),
);