<?php
/**
 *
 * Schema definition for 'cms_application_block'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['cms_application_page_metadata'] = [
    'metadata_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'page_id' => [
        'type' => 'int(11)',
        'foreign_key' => [
            'table' => 'cms_application_page',
            'column' => 'page_id',
            'name' => 'cms_application_page_metadata_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'FK_CMS_APPLICATION_PAGE_METADATA_PAGE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'code' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'type' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ],
    'payload' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'position' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_active' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ]
];
