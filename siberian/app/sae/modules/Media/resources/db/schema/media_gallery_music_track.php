<?php
/**
 *
 * Schema definition for 'media_gallery_music_track'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_gallery_music_track'] = [
    'track_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'album_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => [
            'table' => 'media_gallery_music_album',
            'column' => 'album_id',
            'name' => 'media_gallery_music_track_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_ALBUM_ID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ],
    ],
    'gallery_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => [
            'table' => 'media_gallery_music',
            'column' => 'gallery_id',
            'name' => 'media_gallery_music_track_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_GALLERY_ID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ],
    ],
    'name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'duration' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'artwork_url' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'artist_name' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'album_name' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'price' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'currency' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'purchase_url' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'stream_url' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'type' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'position' => [
        'type' => 'int(11)',
    ],
];
