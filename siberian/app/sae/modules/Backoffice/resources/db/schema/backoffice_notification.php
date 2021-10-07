<?php
/**
 *
 * Schema definition for 'backoffice_notification'
 *
 * Last update: 2021-10-07
 *
 */
$schemas = $schemas ?? [];
$schemas['backoffice_notification'] = [
    'notification_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'original_notification_id' => [
        'type' => 'int(11) unsigned',
    ],
    'title' => [
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'description' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'link' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_high_priority' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'source' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'updates',
    ],
    'type' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'information',
    ],
    'object_type' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'object_id' => [
        'type' => 'int(11) unsigned',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_read' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'sent_at' => [
        'type' => 'datetime',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];