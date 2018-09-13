<?php
/**
 *
 * Schema definition for 'template_design_block'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['template_design_block'] = [
    'design_block_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'design_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'template_design',
            'column' => 'design_id',
            'name' => 'template_design_block_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_DESIGN_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'block_id' => [
        'type' => 'int(11)',
        'foreign_key' => [
            'table' => 'template_block',
            'column' => 'block_id',
            'name' => 'template_design_block_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_BLOCK_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'background_color' => [
        'type' => 'varchar(11)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'color' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'border_color' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'image_color' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'image_opacity' => [
        'type' => 'tinyint(4)',
        'default' => 100
    ],
    'background_opacity' => [
        'type' => 'tinyint(4)',
        'default' => 100
    ],
    'text_opacity' => [
        'type' => 'tinyint(4)',
        'default' => 100
    ],
    'border_opacity' => [
        'type' => 'tinyint(4)',
        'default' => 100
    ]
];