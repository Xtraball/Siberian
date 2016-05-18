<?php
/**
 *
 * Schema definition for 'acl_role'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['acl_role'] = array(
    'role_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'code' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'label' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);