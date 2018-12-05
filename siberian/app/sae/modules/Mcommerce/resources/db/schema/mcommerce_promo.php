<?php
/**
 *
 * Schema definition for 'mcommerce_promo'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_promo'] = [
    'promo_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'mcommerce_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_promo_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_MCOMMERCE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'type' => [
        'type' => 'enum(\'fixed\',\'percentage\')',
        'default' => 'fixed',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'minimum_amount' => [
        'default' => 0,
        'type' => 'double',
    ],
    'discount' => [
        'type' => 'double',
    ],
    'label' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'code' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'enabled' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'use_once' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'hidden' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'points' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'valid_until' => [
        'is_null' => true,
        'type' => 'datetime',
    ],
];