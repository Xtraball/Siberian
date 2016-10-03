<?php
/**
 *
 * Schema definition for 'cms_application_block'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['cms_application_page_metadata'] = array(
    'metadata_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'page_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'cms_application_page',
            'column' => 'page_id',
            'name' => 'cms_application_page_metadata_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'FK_CMS_APPLICATION_PAGE_METADATA_PAGE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'code' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'type' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'is_null' => true,
        'collation' => 'utf8_unicode_ci',
    ),
    'payload' => array(
        'type' => 'text',
        'is_null' => true,
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'is_active' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    )
);
