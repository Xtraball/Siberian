<?php
/**
 *
 * Schema definition for 'application_option_preview'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application_option_preview'] = array(
    'preview_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'option_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option',
            'column' => 'option_id',
            'name' => 'FK_APPLICATION_OPTION_OPTION_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'option_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
);