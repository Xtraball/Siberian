<?php
/**
 *
 * Schema definition for 'catalog_product_folder_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_product_folder_category'] = array(
    'product_id' => array(
        'type' => 'int(11)',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'catalog_product',
            'column' => 'product_id',
            'name' => 'catalog_product_folder_category_ibfk_2',
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
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'folder_category',
            'column' => 'category_id',
            'name' => 'catalog_product_folder_category_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
    ),
);