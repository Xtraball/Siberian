<?php
/**
 * Schema definition for 'fw_rule'
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['fw_rule'] = [
    'rule_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'type' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'value' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
];