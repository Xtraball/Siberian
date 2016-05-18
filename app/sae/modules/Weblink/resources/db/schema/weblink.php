<?php
/**
 *
 * Schema definition for 'weblink'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['weblink'] = array(
    'weblink_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'type_id' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'cover' => array(
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