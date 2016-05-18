<?php
/**
 *
 * Schema definition for 'media_gallery_image_instagram'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['media_gallery_image_instagram'] = array(
    'gallery_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'param_instagram' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'order_by' => array(
        'type' => 'enum(\'updated\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);