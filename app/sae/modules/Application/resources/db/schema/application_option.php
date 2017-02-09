<?php
/**
 *
 * Schema definition for 'application_option'
 *
 * Last update: 2016-07-22
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_option'] = array(
    'option_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_category',
            'column' => 'category_id',
            'name' => 'FK_APPLICATION_OPTION_CATEGORY_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_APPLICATION_OPTION_CATEGORY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'library_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'media_library',
            'column' => 'library_id',
            'name' => 'application_option_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'NO ACTION',
        ),
        'index' => array(
            'key_name' => 'KEY_LIBRARY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'icon_id' => array(
        'type' => 'int(11)',
    ),
    'code' => array(
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'name' => array(
        'type' => 'varchar(25)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'subtitle' => array(
        'type' => 'varchar(512)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ),
    'model' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'desktop_uri' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'mobile_uri' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'mobile_view_uri' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'mobile_view_uri_parameter' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'mobile_uris' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'only_once' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'is_ajax' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'social_sharing_is_available' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_my_account' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_nickname' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_ranking' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'custom_fields' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    )
);
