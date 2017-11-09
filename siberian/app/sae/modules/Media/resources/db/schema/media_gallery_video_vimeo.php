<?php
/**
 *
 * Schema definition for 'media_gallery_video_vimeo'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['media_gallery_video_vimeo'] = array(
    'gallery_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
        'foreign_key' => array(
            'table' => 'media_gallery_video',
            'column' => 'gallery_id',
            'name' => 'media_gallery_video_vimeo_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
    ),
    'type' => array(
        'type' => 'enum(\'user\',\'group\',\'channel\',\'album\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'param' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);