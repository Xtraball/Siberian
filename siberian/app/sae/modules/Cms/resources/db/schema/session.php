<?php
/**
 *
 * Schema definition for 'session'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = $schemas ?? [];
$schemas['session'] = [
    'session_id' => [
        'type' => 'char(32)',
        'primary' => true,
    ],
    'modified' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'lifetime' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'data' => [
        'type' => 'mediumtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'source' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
];