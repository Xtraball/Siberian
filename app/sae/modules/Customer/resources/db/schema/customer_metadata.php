<?php
/**
 *
 * Schema definition for 'customer_social'
 *
 * Last update: 2016-07-22
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['customer_metadata'] = array(
    'id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'customer_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'customer',
            'column' => 'customer_id',
            'name' => 'customer_metadata_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'code' => array(
        'type' => 'varchar(25)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'datas' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);
