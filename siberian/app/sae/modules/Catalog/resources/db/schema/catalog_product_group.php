<?php
/**
 *
 * Schema definition for 'catalog_product_group'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['catalog_product_group'] = array(
    'group_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'title' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_required' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'as_checkbox' => array(
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