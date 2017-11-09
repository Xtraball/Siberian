<?php
/**
 *
 * Schema definition for 'cms_application_page_block'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['cms_application_page_block'] = array(
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'block_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'page_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
);