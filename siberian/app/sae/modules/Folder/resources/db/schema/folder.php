<?php
/**
 *
 * Schema definition for 'folder'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['folder'] = array(
    'folder_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'folder_ibfk_1',
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
    'root_category_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'folder_category',
            'column' => 'category_id',
            'name' => 'folder_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_CAT_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'version' => array(
        'type' => 'int(1)',
        'default' => '1',
    ),
    'show_search' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'card_design' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'allow_line_return' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    )
);