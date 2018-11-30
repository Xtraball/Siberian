<?php
/**
 *
 * Schema definition for 'mcommerce_tax'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['mcommerce_tax'] = [
    'tax_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'mcommerce_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'mcommerce',
            'column' => 'mcommerce_id',
            'name' => 'mcommerce_tax_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'KEY_MCOMMERCE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'name' => [
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'rate' => [
        'type' => 'double',
    ],
    'type' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];