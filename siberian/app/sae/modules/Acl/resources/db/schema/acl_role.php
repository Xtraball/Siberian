<?php
/**
 *
 * Schema definition for 'acl_role'
 *
 * Last update: 2018-08-27
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['acl_role'] = [
    'role_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'code' => [
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'label' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'parent_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'default' => '1',
    ],
    'is_self_assignable' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
];
