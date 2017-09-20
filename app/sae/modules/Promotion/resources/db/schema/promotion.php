<?php
/**
 *
 * Schema definition for 'promotion'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['promotion'] = array(
    'promotion_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'promotion_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'title' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'picture' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'thumbnail' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'description' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'conditions' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_unique' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'end_at' => array(
        'type' => 'date',
        'is_null' => true,
    ),
    'force_validation' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'is_active' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'condition_type' => array(
        'type' => 'varchar(9)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'condition_number_of_points' => array(
        'type' => 'tinyint(2)',
        'is_null' => true,
    ),
    'condition_period_number' => array(
        'type' => 'tinyint(2)',
        'is_null' => true,
    ),
    'condition_period_type' => array(
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_shared' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'owner' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'unlock_by' => array(
        'type' => 'enum(\'account\',\'qrcode\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'account',
    ),
    'unlock_code' => array(
        'type' => 'varchar(32)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);