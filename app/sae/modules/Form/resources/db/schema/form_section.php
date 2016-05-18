<?php
/**
 *
 * Schema definition for 'form_section'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['form_section'] = array(
    'section_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(10) unsigned',
    ),
    'name' => array(
        'type' => 'varchar(50)',
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