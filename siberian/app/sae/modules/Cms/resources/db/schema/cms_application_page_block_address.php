<?php
/**
 *
 * Schema definition for 'cms_application_page_block_address'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['cms_application_page_block_address'] = [
    'address_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'cms_application_page_block',
            'column' => 'value_id',
            'name' => 'cms_application_page_block_address_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'label' => [
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'address' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'phone' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'website' => [
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'latitude' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'longitude' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'show_phone' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'show_website' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'show_address' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'show_geolocation_button' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'rank' => [
        'type' => 'int(11) unsigned',
        'default' => '0',
        'is_null' => true,
    ],
];