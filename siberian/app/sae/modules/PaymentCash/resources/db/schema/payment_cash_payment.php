<?php
/**
 *
 * Schema definition for 'payment_cash_payment'
 *
 * Last update: 2021-02-08
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['payment_cash_payment'] = [
    'cash_payment_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'currency' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
    ],
    'amount' => [
        'type' => 'decimal(19,4)',
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
    ],
    'status' => [
        'type' => 'varchar(128)',
        'charset' => 'utf-8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'created',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
