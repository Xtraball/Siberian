
<?php
/**
 *
 * Schema definition for 'contact'
 *
 * Last update: 2019-03-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['contact'] = [
    'contact_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'contact_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'name' => [
        'type' => 'longtext',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'description' => [
        'type' => 'longtext',
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
    'facebook' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'twitter' => [
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
    'cover' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'email' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'civility' => [
        'type' => 'varchar(5)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'firstname' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'lastname' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'street' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'postcode' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'city' => [
        'type' => 'varchar(100)',
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
    'latitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'longitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'phone' => [
        'type' => 'varchar(15)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'display_locate_action' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'version' => [
        'type' => 'tinyint(1)',
        'default' => "1",
    ],
    'design' => [
        'type' => 'varchar(32)',
        'default' => "card",
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
