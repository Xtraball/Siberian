<?php
/**
 *
 * Schema definition for 'cron'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_ios_autobuild_information'] = array(
    'id' => array(
        'auto_increment' => true,
        'type' => 'int(11) unsigned',
        'primary' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'application_ios_autobuild_information_app_id_kk',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
    ),
    'want_to_autopublish' => array(
        'type' => 'boolean',
    ),
    'itunes_login' => array(
        'type' => 'varchar(255)',
    ),
    'itunes_password' => array(
        'type' => 'varchar(255)',
    ),
    //build server will return it and siberiancms will check it, it's for security purpose
    'token' => array(
        'type' => 'varchar(255)',
        'index' => array(
            'key_name' => 'TOKEN',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ),
    ),
    'has_ads' => array(
        'type' => 'boolean',
    ),
    'has_bg_locate' => array(
        'type' => 'boolean',
    ),
    'has_audio' => array(
        'type' => 'boolean',
    ),
    'languages' => array(
        'type' => 'text',
    ),
    'last_start' => array(
        'type' => 'timestamp',
    ),
    //to know local build date
    'last_success' => array(
        'type' => 'timestamp',
    ),
    'last_finish' => array(
        'type' => 'timestamp',
    ),
    'last_build_status' => array(
        'type' => 'enum(\'pending\',\'queued\',\'building\',\'success\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'pending',
    ),
    'last_builded_version' => array(
        'type' => 'varchar(255)',
    ),
    'last_builded_ipa_link' => array(
        'type' => 'varchar(255)',
    ),
    'error_message' => array(
        'type' => 'text',
    ),
);