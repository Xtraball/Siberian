<?php
/**
 *
 * Schema definition for 'discount'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['discount'] = [
    'discount_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'DISCOUNT_VID_AOV_VID',
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
    'title' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'picture' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'thumbnail' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'description' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'conditions' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_unique' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'end_at' => [
        'type' => 'date',
        'is_null' => true,
    ],
    'force_validation' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_active' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'condition_type' => [
        'type' => 'varchar(9)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'condition_number_of_points' => [
        'type' => 'tinyint(2)',
        'is_null' => true,
    ],
    'condition_period_number' => [
        'type' => 'tinyint(2)',
        'is_null' => true,
    ],
    'condition_period_type' => [
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_shared' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'owner' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'unlock_by' => [
        'type' => 'enum(\'account\',\'qrcode\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'account',
    ],
    'unlock_code' => [
        'type' => 'varchar(32)',
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