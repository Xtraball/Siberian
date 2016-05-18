<?php
/**
 *
 * Schema definition for 'template_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['template_category'] = array(
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'name' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ),
    'code' => array(
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ),
);