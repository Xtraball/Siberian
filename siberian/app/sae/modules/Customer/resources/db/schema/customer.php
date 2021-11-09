<?php
/**
 *
 * Schema definition for 'customer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = $schemas ?? [];
$schemas['customer'] = [
    'customer_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11)',
    ],
    'civility' => [
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'firstname' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'lastname' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'nickname' => [
        'type' => 'varchar(16)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'phone' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'mobile' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ],
    'birthdate' => [
        'type' => 'bigint(20) unsigned',
    ],
    'email' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'password' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'language' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_custom_image' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'show_in_social_gaming' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'can_access_locked_features' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_active' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'is_deleted' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'gdpr_token' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'privacy_policy' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'communication_agreement' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'session_uuid' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
