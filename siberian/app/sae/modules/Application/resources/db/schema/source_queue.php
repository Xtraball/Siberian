<?php
/**
 *
 * Schema definition for 'cron'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['source_queue'] = [
    'source_queue_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'name' => [
        'type' => 'varchar(128)',
    ],
    'url' => [
        'type' => 'text',
    ],
    'path' => [
        'type' => 'text',
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
    ],
    'protocol' => [
        'type' => 'varchar(16)',
    ],
    'host' => [
        'type' => 'varchar(128)',
    ],
    'type' => [
        'type' => 'varchar(32)',
    ],
    'design_code' => [
        'type' => 'varchar(32)',
    ],
    'user_id' => [
        'type' => 'int(11) unsigned',
    ],
    'user_type' => [
        'type' => 'varchar(16)',
    ],
    'status' => [
        'type' => 'enum(\'queued\',\'building\',\'success\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ],
    'is_apk_service' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'apk_path' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ],
    'apk_message' => [
        'type' => 'text',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ],
    'apk_status' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ],
    'build_time' => [
        'type' => 'int(11) unsigned',
    ],
    'build_start_time' => [
        'type' => 'int(11) unsigned',
    ],
    'is_autopublish' => [
        'type' => 'tinyint(1) unsigned',
    ],
    'is_refresh_pem' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'log' => [
        'type' => 'longtext',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];