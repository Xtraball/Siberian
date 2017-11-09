<?php
/**
 *
 * Schema definition for 'catalog_product_group_value'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_product_group_value'] = array(
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'product_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'catalog_product',
            'column' => 'product_id',
            'name' => 'catalog_product_group_value_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_CATALOG_PRODUCT_PRODUCT_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'group_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'catalog_product_group',
            'column' => 'group_id',
            'name' => 'catalog_product_group_value_ibfk_2',
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
);