<?php
/**
 *
 * Schema definition for 'template_block_app'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['template_block_app'] = array(
    'block_id' => array(
        'type' => 'int(11)',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'template_block',
            'column' => 'block_id',
            'name' => 'template_block_app_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'template_block_app_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_APPLICATION_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'color' => array(
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_color' => array(
        'type' => 'varchar(11)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'border_color' => array(
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'image_color' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'image_opacity' => array(
        'type' => 'tinyint(4)',
        'default' => 100
    ),
    'background_opacity' => array(
        'type' => 'tinyint(4)',
        'default' => 100
    ),
    'text_opacity' => array(
        'type' => 'tinyint(4)',
        'default' => 100
    ),
    'border_opacity' => array(
        'type' => 'tinyint(4)',
        'default' => 100
    ),
    'created_at' => array(
        'type' => 'datetime',
        'default' => '2016-10-14 00:00:01',
    ),
    'updated_at' => array(
        'type' => 'datetime',
        'default' => '2016-10-14 00:00:01',
    ),
);