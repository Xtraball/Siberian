<?php
/**
 *
 * Schema definition for 'payment_method_payment'
 *
 * Last update: 2019-09-11
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['payment_method_payment'] = [
    'payment_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'method_code' => [
        'type' => 'varchar(256)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'method_id' => [
        'type' => 'varchar(256)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
