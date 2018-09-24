<?php
/**
 *
 * Schema definition for 'template_design_content'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['template_design_content'] = [
    'content_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'design_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'template_design',
            'column' => 'design_id',
            'name' => 'FK_TEMPLATE_DESIGN_CONTENT_DESIGN_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'design_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'option_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option',
            'column' => 'option_id',
            'name' => 'FK_TEMPLATE_DESIGN_CONTENT_OPTION_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'option_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'option_tabbar_name' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
    'option_icon' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
    'option_background_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
];