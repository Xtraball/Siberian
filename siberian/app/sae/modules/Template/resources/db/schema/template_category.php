<?php
/**
 *
 * Schema definition for 'template_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['template_category'] = [
    'category_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'original_name' => [
        'type' => 'varchar(128)',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
    'name' => [
        'type' => 'varchar(128)',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
    'code' => [
        'type' => 'varchar(128)',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
];