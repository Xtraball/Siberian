<?php
/**
 *
 * Schema definition for 'message_application_file'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['message_application_file'] = array(
    'file_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'message_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'message_application',
            'column' => 'message_id',
            'name' => 'FK_MESSAGE_APPLICATION_FILE_MESSAGE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'message_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'file' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);