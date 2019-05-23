<?php
/**
 *
 * Schema definition for 'weather'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['weather'] = [
    'weather_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_WEATHER_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'city' => [
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'country_code' => [
        'type' => 'varchar(3)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'unit' => [
        'type' => 'varchar(1)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'f',
    ],
    'woeid' => [
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
];