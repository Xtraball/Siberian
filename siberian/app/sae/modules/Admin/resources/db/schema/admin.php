<?php
/**
 *
 * Schema definition for 'admin'
 *
 * Last update: 2020-09-10
 *
 */
$schemas = $schemas ?? [];
$schemas['admin'] = [
    'admin_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'parent_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'index' => [
            'key_name' => 'KEY_PARENT_ID',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ],
    ],
    'role_id' => [
        'type' => 'int(11) unsigned',
        'default' => '1',
        'index' => [
            'key_name' => 'role_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
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
    'is_allowed_to_add_pages' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'is_allowed_to_manage_tour' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'publication_access_type' => [
        'type' => 'enum(\'sources\',\'info\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'generate_apk' => [
        'type' => 'enum(\'no\',\'yes\')',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'company' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'website' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'firstname' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'lastname' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'address' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'address2' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'city' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'zip_code' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'region_code' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'region' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'country_code' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'country' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'preferences' => [
        'type' => 'longtext',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'phone' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'vat_number' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'accept_tos' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'last_action' => [
        'type' => 'datetime',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
