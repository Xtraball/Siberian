<?php
/**
 *
 * Schema definition for 'application_acl_option'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_acl_option'] = array(
    'application_acl_option_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'app_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'admin_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_APPLICATION_ACL_OPTION_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'resource_code' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);