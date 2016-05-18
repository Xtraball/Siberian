<?php
/**
 *
 * Schema definition for 'mcommerce_store_payment_method'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_store_payment_method'] = array(
    'store_payment_method_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'store_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_store',
            'column' => 'store_id',
            'name' => 'mcommerce_store_payment_method_ibfk_1',
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
    'method_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_payment_method',
            'column' => 'method_id',
            'name' => 'mcommerce_store_payment_method_ibfk_2',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_METHOD_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
);