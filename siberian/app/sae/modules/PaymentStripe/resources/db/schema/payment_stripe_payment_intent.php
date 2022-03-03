<?php
/**
 *
 * Schema definition for 'payment_stripe_payment_intent'
 *
 * Last update: 2021-10-11
 *
 */
$schemas = $schemas ?? [];
$schemas['payment_stripe_payment_intent'] = [
    'stripe_payment_intent_id' => [
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
    'app_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'PSPI_KEY_APP_ID',
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
    'pm_token' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'pm_id' => [
        'type' => 'int(11) unsigned',
        'null' => true,
    ],
    'currency' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'confirmation_method' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'capture_method' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'setup_future_usage' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'amount' => [
        'type' => 'decimal(19,4)',
    ],
    'stripe_amount' => [
        'type' => 'varchar(25)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
    ],
    'stripe_customer_token' => [
        'type' => 'int(11) unsigned',
        'null' => true,
    ],
    'status' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'created',
    ],
    'raw_payload' => [
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
