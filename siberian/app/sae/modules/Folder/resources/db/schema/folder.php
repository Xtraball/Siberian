<?php
/**
 *
 * Schema definition for 'folder'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['folder'] = [
    'folder_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'folder_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'root_category_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'folder_category',
            'column' => 'category_id',
            'name' => 'folder_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_CAT_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'version' => [
        'type' => 'int(1)',
        'default' => '1',
    ],
    'show_search' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'card_design' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'allow_line_return' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ]
];