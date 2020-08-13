<?php
/**
 *
 * Schema definition for 'cms_application_page_block_source'
 *
 * Last update: 2020-01-27
 *
 */
$schemas = $schemas ?? [];
$schemas['cms_application_page_block_source'] = [
    'source_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'cms_application_page_block',
            'column' => 'value_id',
            'name' => 'cms_application_page_block_source_ibfk_1',
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
    'original' => [
        'type' => 'longtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'source' => [
        'type' => 'longtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'height' => [
        'type' => 'smallint(1) unsigned',
    ],
    'unit' => [
        'type' => 'varchar(2)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
];
