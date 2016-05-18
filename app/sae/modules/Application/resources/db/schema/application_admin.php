<?php
/**
 *
 * Schema definition for 'application_admin'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_admin'] = array(
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'application_admin_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
    ),
    'admin_id' => array(
        'type' => 'int(11) unsigned',
        'primary' => true,
        'foreign_key' => array(
            'table' => 'admin',
            'column' => 'admin_id',
            'name' => 'FK_APPLICATION_ADMIN_ADMIN_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'sign_id_idxfk',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'is_allowed_to_add_pages' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
);