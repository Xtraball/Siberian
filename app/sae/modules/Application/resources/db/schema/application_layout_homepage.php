<?php
/**
 *
 * Schema definition for 'application_layout_homepage'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_layout_homepage'] = array(
    'layout_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'visibility' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'homepage',
    ),
    'code' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
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
    'preview_new' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'use_more_button' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_horizontal_scroll' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_homepage_slider' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_subtitle' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'number_of_displayed_icons' => array(
        'type' => 'tinyint(2)',
        'is_null' => true,
    ),
    'position' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'bottom',
    ),
    'options' => array(
        'type' => 'longtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'order' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'is_active' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
);