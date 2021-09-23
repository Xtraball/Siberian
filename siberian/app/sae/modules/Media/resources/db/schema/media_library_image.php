<?php
/**
 *
 * Schema definition for 'media_library_image'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_library_image'] = [
    'image_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'library_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'media_library',
            'column' => 'library_id',
            'name' => 'media_library_image_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_LIBRARY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'link' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'secondary_link' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'thumbnail' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'keywords' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'option_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'can_be_colorized' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'position' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_active' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
];
