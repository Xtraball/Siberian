<?php
/**
 *
 * Schema definition for 'place_customer_note'
 *
 * Last update: 2022-04-04
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['place_customer_note'] = [
    'customer_note_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'KEY_PLACE_CUSTOMER_NOTE_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PLACE_CUSTOMER_NOTE_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'customer',
            'column' => 'customer_id',
            'name' => 'KEY_PLACE_CUSTOMER_NOTE_CUSTOMER_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PLACE_CUSTOMER_NOTE_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'place_id' => [
        'type' => 'int(11)',
        'foreign_key' => [
            'table' => 'cms_application_page',
            'column' => 'page_id',
            'name' => 'KEY_PLACE_CUSTOMER_NOTE_PLACE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PLACE_CUSTOMER_NOTE_PLACE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'note' => [
        'type' => 'longtext',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];