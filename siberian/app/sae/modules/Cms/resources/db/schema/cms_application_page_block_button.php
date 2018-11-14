<?php
/**
 *
 * Schema definition for 'cms_application_page_block_button'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['cms_application_page_block_button'] = [
    'button_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'type_id' => [
        'type' => 'enum(\'link\',\'phone\',\'email\')',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'phone',
    ],
    'content' => [
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'label' => [
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'icon' => [
        'type' => 'varchar(256)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'hide_navbar' => [
        'type' => 'boolean',
        'default' => "0"
    ],
    'use_external_app' => [
        'type' => 'boolean',
        'default' => "0"
    ]
];