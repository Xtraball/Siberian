<?php
/**
 *
 * Schema definition for 'firebase_credential'
 *
 * Last update: 2018-06-06
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['firebase_credential'] = [
    'credential_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'sender_id' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'server_key' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'admin_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'google_service' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
