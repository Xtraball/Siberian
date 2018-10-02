<?php
/**
 *
 * Schema definition for 'cms_application_page_block'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['cms_application_page_block'] = [
    'value_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'block_id' => [
        'type' => 'int(11) unsigned',
    ],
    'page_id' => [
        'type' => 'int(11) unsigned',
    ],
    'position' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
];