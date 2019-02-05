<?php
/**
 *
 * Schema definition for 'cron'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['application_ios_autobuild_information'] = [
    'id' => [
        'auto_increment' => true,
        'type' => 'int(11) unsigned',
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'application_ios_autobuild_information_app_id_kk',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
    ],
    'want_to_autopublish' => [
        'type' => 'boolean',
    ],
    'refresh_pem' => [
        'type' => 'boolean',
        'default' => '0'
    ],
    'account_type' => [
        'type' => 'varchar(16)',
    ],
    'itunes_login' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'itunes_password' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'itunes_original_login' => [
        'type' => 'varchar(255)',
        'is_null' => true,
    ],
    'cyphered_credentials' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'team_id' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'team_name' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'itc_provider' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'teams' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    //build server will return it and siberiancms will check it, it's for security purpose
    'token' => [
        'type' => 'varchar(255)',
        'index' => [
            'key_name' => 'TOKEN',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ],
    ],
    'has_ads' => [
        'type' => 'boolean',
    ],
    'has_bg_locate' => [
        'type' => 'boolean',
    ],
    'has_audio' => [
        'type' => 'boolean',
    ],
    'languages' => [
        'type' => 'text',
    ],
    'last_start' => [
        'type' => 'timestamp',
    ],
    //to know local build date
    'last_success' => [
        'type' => 'timestamp',
    ],
    'last_finish' => [
        'type' => 'timestamp',
    ],
    'last_build_status' => [
        'type' => 'enum(\'pending\',\'queued\',\'building\',\'success\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'pending',
    ],
    'last_builded_version' => [
        'type' => 'varchar(255)',
    ],
    'last_builded_ipa_link' => [
        'type' => 'varchar(255)',
    ],
    'error_message' => [
        'type' => 'text',
    ],
];