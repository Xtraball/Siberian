<?php
/**
 *
 * Schema definition for 'payment_stripe_charge'
 *
 * Last update: 2022-01-21
 *
 */
$schemas = $schemas ?? [];
$schemas['payment_stripe_charge'] = [
    'stripe_charge_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
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
