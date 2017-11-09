<?php
/**
 *
 * Schema definition for 'acl_resource_role'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['acl_resource_role'] = array(
    'resource_id' => array(
        'type' => 'int(11) unsigned',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'acl_resource',
            'column' => 'resource_id',
            'name' => 'FK_RESOURCE_ROLE_RESOURCE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
    ),
    'role_id' => array(
        'type' => 'int(11) unsigned',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'acl_role',
            'column' => 'role_id',
            'name' => 'FK_RESOURCE_ROLE_ROLE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_RESOURCE_ROLE_ROLE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
);