<?php
/**
 *
 * Schema definition for 'backoffice_notification'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['backoffice_notification'] = array(
    'notification_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'original_notification_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'title' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'description' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'link' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_high_priority' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'is_read' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);