<?php
/**
 * Customer request app email
 *
 * @version 1.0.0
 */
$schemas = $schemas ?? [];
$schemas['customer_request_app'] = [
    'request_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
    ],
    'email' => [
        'type' => 'varchar(255)',
    ],
    'token' => [
        'type' => 'varchar(255)',
    ],
    'status' => [
        'type' => 'varchar(255)',
        'default' => 'pending',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];