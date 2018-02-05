<?php
/**
 *
 * Schema definition for 'application_option'
 *
 * Last update: 2016-07-22
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['application_option'] = [
    'option_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'category_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_category',
            'column' => 'category_id',
            'name' => 'FK_APPLICATION_OPTION_CATEGORY_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'FK_APPLICATION_OPTION_CATEGORY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'library_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'media_library',
            'column' => 'library_id',
            'name' => 'application_option_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'NO ACTION',
        ],
        'index' => [
            'key_name' => 'KEY_LIBRARY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'icon_id' => [
        'type' => 'int(11)',
    ],
    'code' => [
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'name' => [
        'type' => 'varchar(25)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'subtitle' => [
        'type' => 'varchar(512)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'model' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'desktop_uri' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'mobile_uri' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'mobile_view_uri' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'mobile_view_uri_parameter' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'mobile_uris' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'only_once' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'is_ajax' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'position' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'social_sharing_is_available' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'use_my_account' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'use_nickname' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'use_ranking' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'custom_fields' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'backoffice_description' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_enabled' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
];
