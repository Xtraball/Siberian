<?php
/**
 *
 * Schema definition for 'payment_stripe_log'
 *
 * Last update: 2022-01-21
 *
 */
$schemas = $schemas ?? [];
$schemas['payment_stripe_log'] = [
    'stripe_log_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'message' => [
        'type' => 'text',
        'null' => true,
    ],
    'raw_payload' => [
        'type' => 'longtext',
        'null' => true,
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
