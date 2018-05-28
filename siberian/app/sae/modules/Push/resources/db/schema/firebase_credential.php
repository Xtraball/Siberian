<?php
/**
 *
 * Schema definition for 'push_messages'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['firebase_credential'] = [
    'credential_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'email' => [
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'cyphered' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'project_number' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'raw_projects' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'admin_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
