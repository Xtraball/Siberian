<?php
/**
 *
 * Schema definition for 'mcommerce_promo_log'
 *
 * Last update: 2016-11-16
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_promo_log'] = array(
    'log_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'promo_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce_promo',
            'column' => 'promo_id',
            'name' => 'mcommerce_promo_log_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_MCOMMERCE_PROMO_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'ttc' => array(
        'type' => 'decimal(12,2)',
    ),
    'discount' => array(
        'type' => 'decimal(12,2)',
    ),
    'total' => array(
        'type' => 'decimal(12,2)',
    ),
    'customer_identifier' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'customer_uuid' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'code' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);