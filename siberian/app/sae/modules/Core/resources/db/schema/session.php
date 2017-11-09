<?php
/**
 *
 * Schema definition for 'session'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['session'] = array(
    'session_id' => array(
        'type' => 'char(32)',
        'primary' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'modified' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'lifetime' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'data' => array(
        'type' => 'mediumtext',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);