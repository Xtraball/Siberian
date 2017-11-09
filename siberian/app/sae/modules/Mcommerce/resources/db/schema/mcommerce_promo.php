<?php
/**
 *
 * Schema definition for 'mcommerce_promo'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce_promo'] = array(
    'promo_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'mcommerce_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_promo_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_MCOMMERCE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'type' => array(
        'type' => 'enum(\'fixed\',\'percentage\')',
        'default' => 'fixed',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'minimum_amount' => array(
        'default' => 0,
        'type' => 'decimal(12,2)',
    ),
    'discount' => array(
        'type' => 'decimal(12,2)',
    ),
    'label' => array(
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
    'enabled' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'use_once' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'hidden' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'points' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'valid_until' => array(
        'is_null' => true,
        'type' => 'datetime',
    ),
);