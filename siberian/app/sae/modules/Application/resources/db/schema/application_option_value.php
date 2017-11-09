<?php
/**
 *
 * Schema definition for 'application_option_value'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_option_value'] = array(
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'application_option_value_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'option_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option',
            'column' => 'option_id',
            'name' => 'application_option_value_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_OPTION_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'layout_id' => array(
        'type' => 'int(11) unsigned',
        'default' => '1',
    ),
    'icon_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'folder_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'folder_category_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'folder_category_position' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'tabbar_name' => array(
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'tabbar_subtitle' => array(
        'type' => 'varchar(512)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'icon' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_landscape_image' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_visible' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'is_active' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'social_sharing_is_active' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'touched_at' => array(
        'type' => 'int(11)',
        'is_null' => false,
        'default' => -1,
    ),
    'expires_at' => array(
        'type' => 'int(11)',
        'is_null' => false,
        'default' => -1,
    ),
);