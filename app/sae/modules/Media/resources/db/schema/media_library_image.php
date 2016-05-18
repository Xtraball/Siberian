<?php
/**
 *
 * Schema definition for 'media_library_image'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['media_library_image'] = array(
    'image_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'library_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'media_library',
            'column' => 'library_id',
            'name' => 'media_library_image_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_LIBRARY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'link' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'secondary_link' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'thumbnail' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'option_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'can_be_colorized' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
);