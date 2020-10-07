<?php
/**
 *
 * Schema definition for 'media_gallery_image_custom'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_gallery_image_custom'] = [
    'image_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'gallery_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'media_gallery_image',
            'column' => 'gallery_id',
            'name' => 'media_gallery_image_custom_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_GALLERY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'url' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'title' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'description' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'position' => [
        'type' => 'int(11)',
        'default' => '0',
        'is_null' => true,
    ],
];
