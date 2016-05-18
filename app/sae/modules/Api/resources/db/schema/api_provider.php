<?php
/**
 *
 * Schema definition for 'api_provider'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['api_provider'] = array(
    'provider_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'code' => array(
        'type' => 'varchar(30)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'name' => array(
        'type' => 'varchar(60)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'icon' => array(
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);