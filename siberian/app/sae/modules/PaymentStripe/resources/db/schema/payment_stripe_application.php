<?php
/**
 *
 * Schema definition for 'payment_stripe_application'
 *
 * Last update: 2021-10-11
 *
 */
$schemas = $schemas ?? [];
$schemas['payment_stripe_application'] = [
    'stripe_application_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'PSA_KEY_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'publishable_key' => [
        'type' => 'varchar(256)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'secret_key' => [
        'type' => 'varchar(256)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_enabled' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_sandbox' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
