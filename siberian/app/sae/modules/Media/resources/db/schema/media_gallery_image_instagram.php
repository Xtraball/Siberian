<?php
/**
 *
 * Schema definition for 'media_gallery_image_instagram'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['media_gallery_image_instagram'] = [
    'gallery_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'param_instagram' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'order_by' => [
        'type' => 'enum(\'updated\')',
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
