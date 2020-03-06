<?php
/**
 *
 * Schema definition for 'weblink_link'
 *
 * Last update: 2019-12-11
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['weblink_link'] = [
    'link_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'weblink_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'weblink',
            'column' => 'weblink_id',
            'name' => 'weblink_link_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_WEBLINK_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'picto' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'title' => [
        'type' => 'varchar(40)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'url' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'options' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'external_browser' => [
        'type' => 'tinyint(1)',
        'default' => '0'
    ],
    'custom_tab' => [
        'type' => 'tinyint(1)',
        'default' => '0'
    ],
    'in_app_browser' => [
        'type' => 'tinyint(1)',
        'default' => '0'
    ],
    'hide_navbar' => [
        'type' => 'tinyint(1)',
        'default' => '0'
    ],
    'use_external_app' => [
        'type' => 'boolean',
        'default' => "0"
    ],
    'position' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'version' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
];