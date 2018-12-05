<?php
/**
 *
 * Schema definition for 'mcommerce_promo_log'
 *
 * Last update: 2016-11-16
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_promo_log'] = [
    'log_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'promo_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce_promo',
            'column' => 'promo_id',
            'name' => 'mcommerce_promo_log_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_MCOMMERCE_PROMO_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'ttc' => [
        'type' => 'double',
    ],
    'discount' => [
        'type' => 'double',
    ],
    'total' => [
        'type' => 'double',
    ],
    'customer_identifier' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_uuid' => [
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
];