<?php
/**
 *
 * Schema definition for 'template_design_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['template_design_category'] = [
    'design_category_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'design_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'design_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'category_id' => [
        'type' => 'int(11) unsigned',
        'index' => [
            'key_name' => 'category_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
];