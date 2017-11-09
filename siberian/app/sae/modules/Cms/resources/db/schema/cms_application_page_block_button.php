<?php
/**
 *
 * Schema definition for 'cms_application_page_block_button'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['cms_application_page_block_button'] = array(
    'button_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'type_id' => array(
        'type' => 'enum(\'link\',\'phone\',\'email\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'phone',
    ),
    'content' => array(
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'label' => array(
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'icon' => array(
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'hide_navbar' => array(
        'type' => 'boolean',
        'default' => "0"
    ),
    'use_external_app' => array(
        'type' => 'boolean',
        'default' => "0"
    )
);