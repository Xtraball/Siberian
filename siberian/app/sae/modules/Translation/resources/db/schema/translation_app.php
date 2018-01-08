<?php
/**
 * Schema definition for 'translation_app'
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['translation_app'] = [
    'translation_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'filename' => [
        'type' => 'varchar(128)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'origin' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'target' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'source' => [
        'type' => 'mediumtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'value' => [
        'type' => 'mediumtext',
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