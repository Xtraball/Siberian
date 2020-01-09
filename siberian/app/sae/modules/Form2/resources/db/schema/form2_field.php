<?php
/**
 *
 * Schema definition for "form2_field"
 *
 * Last update: 2019-12-31
 *
 */
$schemas = $schemas ?? [];
$schemas['form2_field'] = [
    'field_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_FORM2_FIELD_VID_AOV_VID',
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
    'label' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'field_type' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'number_min' => [
        'type' => 'double',
    ],
    'number_max' => [
        'type' => 'double',
    ],
    'number_step' => [
        'type' => 'double',
    ],
    'field_options' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'richtext' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'clickwrap' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'clickwrap_modaltitle' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'clickwrap_richtext' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'image' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'image_addpicture' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'image_addanotherpicture' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'limit' => [
        'type' => 'tinyint(1)',
    ],
    'date_format' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'date_days' => [
        'type' => 'varchar(16)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'datetime_format' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'datetime_days' => [
        'type' => 'varchar(16)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_required' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'default_value' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'position' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
];