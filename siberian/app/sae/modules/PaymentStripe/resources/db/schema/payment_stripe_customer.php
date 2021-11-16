<?php
/**
 *
 * Schema definition for 'payment_stripe_customer'
 *
 * Last update: 2021-10-11
 *
 */
$schemas = $schemas ?? [];
$schemas['payment_stripe_customer'] = [
    'stripe_customer_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'psc_customer_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'admin_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'psc_admin_id',
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
