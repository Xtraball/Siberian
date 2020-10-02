<?php
/**
 *
 * Schema definition for 'media_gallery_video_youtube'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_gallery_video_youtube'] = [
    'gallery_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
        'foreign_key' => [
            'table' => 'media_gallery_video',
            'column' => 'gallery_id',
            'name' => 'media_gallery_video_youtube_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
    ],
    'type' => [
        'type' => 'enum(\'user\',\'channel\',\'search\',\'favorite\',\'playlist\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'param' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'order_by' => [
        'type' => 'enum(\'updated\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
];
