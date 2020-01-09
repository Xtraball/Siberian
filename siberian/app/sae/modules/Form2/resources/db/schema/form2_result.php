<?php
/**
 *
 * Schema definition for 'form_result'
 *
 */
$schemas = $schemas ?? [];
$schemas['form2_result'] = [
    'result_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'payload' => [
        'type' => 'longtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'label' => [
        'type' => 'varchar(256)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'timestamp' => [
        'type' => 'int(11)',
    ],
    'is_removed' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];