<?php
/**
 *
 * Schema definition for 'application_option_value'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['application_option_value'] = [
    'value_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'application_option_value_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'option_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option',
            'column' => 'option_id',
            'name' => 'application_option_value_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_OPTION_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'layout_id' => [
        'type' => 'int(11) unsigned',
        'default' => '1',
    ],
    'icon_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'folder_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'folder_category_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'folder_category_position' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'tabbar_name' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'tabbar_subtitle' => [
        'type' => 'varchar(512)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'icon' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_landscape_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_visible' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'position' => [
        'type' => 'int(11) unsigned',
        'default' => '0',
    ],
    'settings' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_active' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'social_sharing_is_active' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'touched_at' => [
        'type' => 'int(11)',
        'is_null' => false,
        'default' => -1,
    ],
    'expires_at' => [
        'type' => 'int(11)',
        'is_null' => false,
        'default' => -1,
    ],
];