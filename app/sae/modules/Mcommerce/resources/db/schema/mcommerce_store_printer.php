<?php
/**
 *
 * Schema definition for 'mcommerce_store_printer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_store_printer'] = array(
    'printer_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'store_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_store',
            'column' => 'store_id',
            'name' => 'mcommerce_store_printer_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_STORE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'email' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'type' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);