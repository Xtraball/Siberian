<?php
/**
 *
 * Schema definition for 'application_option_layout'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_option_layout'] = array(
    'layout_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'code' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'option_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'name' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'preview' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'position' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
);