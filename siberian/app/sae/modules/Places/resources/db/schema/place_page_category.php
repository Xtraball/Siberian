<?php
/**
 *
 * Schema definition for 'place_page_category'
 *
 * Last update: 2018-11-16
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['place_page_category'] = [
    'page_category_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'page_id' => [
        'type' => 'int(11)',
    ],
    'category_id' => [
        'type' => 'int(11)',
    ],
];