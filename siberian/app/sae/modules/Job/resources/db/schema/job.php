<?php
/**
 *
 * Schema definition for 'mcommerce'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['job'] = array(
    'job_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'display_search' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'display_place_icon' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'display_income' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'display_contact' => array(
        'type' => 'enum(\'hidden\',\'contactform\',\'email\',\'both\')',
        'is_null' => false,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'contactform',
    ),
    'title_company' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'Company',
    ),
    'title_place' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'Company',
    ),
    'default_radius' => array(
        'type' => 'tinyint(2)',
        'default' => 4,
    ),
    'distance_unit' => array(
        'type' => 'varchar(4)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'km',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);