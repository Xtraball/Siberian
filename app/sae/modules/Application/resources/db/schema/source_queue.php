<?php
/**
 *
 * Schema definition for 'cron'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['source_queue'] = array(
    'source_queue_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'name' => array(
        'type' => 'varchar(128)',
    ),
    'url' => array(
        'type' => 'text',
    ),
    'path' => array(
        'type' => 'text',
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'protocol' => array(
        'type' => 'varchar(16)',
    ),
    'host' => array(
        'type' => 'varchar(128)',
    ),
    'type' => array(
        'type' => 'varchar(32)',
    ),
    'design_code' => array(
        'type' => 'varchar(32)',
    ),
    'user_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'user_type' => array(
        'type' => 'varchar(16)',
    ),
    'status' => array(
        'type' => 'enum(\'queued\',\'building\',\'success\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ),
    'build_time' => array(
        'type' => 'int(11) unsigned',
    ),
    'build_start_time' => array(
        'type' => 'int(11) unsigned',
    ),
    'is_autopublish' => array(
        'type' => 'int(1) unsigned',
    ),
    'log' => array(
        'type' => 'longtext',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);