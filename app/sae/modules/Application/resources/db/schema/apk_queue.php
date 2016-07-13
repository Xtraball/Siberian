<?php
/**
 *
 * Schema definition for 'cron'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['apk_queue'] = array(
    'apk_queue_id' => array(
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
    'app_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'user_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'status' => array(
        'type' => 'enum(\'queued\',\'building\',\'success\',\'failed\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
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