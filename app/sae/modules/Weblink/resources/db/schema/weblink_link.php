<?php
/**
 *
 * Schema definition for 'weblink_link'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['weblink_link'] = array(
    'link_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'weblink_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'weblink',
            'column' => 'weblink_id',
            'name' => 'weblink_link_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_WEBLINK_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'picto' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'title' => array(
        'type' => 'varchar(40)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'url' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'hide_navbar' => array(
        'type' => 'boolean',
        'default' => "0"
    ),
    'use_external_app' => array(
        'type' => 'boolean',
        'default' => "0"
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
);