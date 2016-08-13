<?php
/**
 *
 * Schema definition for 'module'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['module'] = array(
    'module_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'name' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'version' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'can_uninstall' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);