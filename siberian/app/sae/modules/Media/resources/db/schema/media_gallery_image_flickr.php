<?php
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_gallery_image_flickr'] = [
    'image_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'gallery_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'media_gallery_image',
            'column' => 'gallery_id',
            'name' => 'media_gallery_image_flickr_ibfk_1',
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
    'identifier' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    // Could be
    'type' => [
        'type' => 'enum(\'people\',\'gallery\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'position' => [
        'type' => 'int(11)',
        'default' => '0',
        'is_null' => true,
    ],
];
