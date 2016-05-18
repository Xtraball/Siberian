<?php
/**
 *
 * Schema definition for 'catalog_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_category'] = array(
    'category_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'parent_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
        'index' => array(
            'key_name' => 'KEY_PARENT_ID',
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
            'name' => 'catalog_category_ibfk_1',
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
    'name' => array(
        'type' => 'varchar(50)',
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