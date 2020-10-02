<?php
/**
 *
 * Schema definition for 'media_gallery_video_itunes'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_gallery_video_itunes'] = [
    'gallery_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
        'foreign_key' => [
            'table' => 'media_gallery_video',
            'column' => 'gallery_id',
            'name' => 'media_gallery_video_itunes_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
    ],
    'param' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
];
