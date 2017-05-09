<?php
/**
 *
 * Schema definition for 'application_device'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_device'] = array(
    'device_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'APPLICATION_DEVICE_APP_ID',
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
    'type_id' => array(
        'type' => 'tinyint(11) unsigned',
    ),
    'status_id' => array(
        'type' => 'tinyint(11) unsigned',
        'is_null' => true,
        'default' => '1',
    ),
    'admob_id' => array(
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'admob_interstitial_id' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'admob_type' => array(
        'type' => 'enum(\'banner\',\'interstitial\',\'videos\',\'banner-interstitial\',\'banner-videos\',\'interstitial-videos\',\'banner-interstitial-videos\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'banner',
    ),
    'owner_admob_id' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'owner_admob_interstitial_id' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'owner_admob_type' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'owner_admob_weight' => array(
        'type' => 'tinyint(4)',
        'default' => 100,
    ),
    'version' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '0.0.1',
    ),
    'build_number' => array(
        'type' => 'integer',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '1',
    ),
    'developer_account_username' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'developer_account_password' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'use_our_developer_account' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'store_url' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'store_pass' => array(
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'store_app_id' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'banner_store_label' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'banner_store_price' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'key_pass' => array(
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'alias' => array(
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'pks' => array(
        'type' => 'blob',
        'is_null' => true,
    ),
);