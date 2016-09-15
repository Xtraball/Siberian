<?php
/**
 *
 * Schema definition for 'radio'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['radio'] = array(
    'radio_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'title' => array(
        'type' => 'varchar(60)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'link' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background' => array(
        'type' => 'varchar(255)',
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