<?php
/**
 *
 * Schema definition for 'media_gallery_image'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['media_gallery_image'] = array(
    'gallery_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'media_gallery_image_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'type_id' => array(
        'type' => 'enum(\'picasa\',\'custom\',\'instagram\',\'flickr\',\'facebook\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'name' => array(
        'type' => 'varchar(25)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'label' => array(
        'type' => 'varchar(25)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'created_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'updated_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
);