<?php
/**
 *
 * Schema definition for 'loyalty_card_password'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['loyalty_card_password'] = array(
    'password_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'name' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'password' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'unlock_code' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'loyalty_card_password_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);