<?php
/**
 *
 * Schema definition for 'folder_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['folder_category'] = array(
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'parent_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => array(
            'table' => 'folder_category',
            'column' => 'category_id',
            'name' => 'folder_category_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_PARENT_ID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ),
    ),
    'type_id' => array(
        'type' => 'enum(\'folder\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'folder',
    ),
    'picture' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'thumbnail' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'title' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'subtitle' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'pos' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'version' => array(
        'type' => 'int(1)',
        'default' => '1',
    ),
    'layout_id' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
    ),
    'show_cover' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'show_title' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'icon_size' => array(
        'type' => 'tinyint(1)',
        'default' => '50',
    ),
    'value_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'folder_category_fk_value_id',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ]
    ],
);