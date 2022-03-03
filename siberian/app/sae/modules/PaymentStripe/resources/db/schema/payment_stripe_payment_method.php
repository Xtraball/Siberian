<?php
/**
 *
 * Schema definition for 'payment_stripe_payment_method'
 *
 * Last update: 2021-10-11
 *
 */
$schemas = $schemas ?? [];
$schemas['payment_stripe_payment_method'] = [
    'stripe_payment_method_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'stripe_customer_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'stripe_customer_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'token' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'test_token' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'type' => [ // "credit-card"
        'type' => 'varchar(20)',
        'null' => true,
    ],
    'brand' => [
        'type' => 'varchar(32)',
        'null' => true,
    ],
    'exp' => [
        'type' => 'varchar(10)',
        'null' => true,
    ],
    'last' => [
        'type' => 'varchar(4)',
        'null' => true,
    ],
    'is_last_used' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'is_favorite' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'raw_payload' => [
        'type' => 'longtext',
        'null' => true,
    ],
    'test_raw_payload' => [
        'type' => 'longtext',
        'null' => true,
    ],
    'is_removed' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
