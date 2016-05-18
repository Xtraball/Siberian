<?php
/**
 *
 * Schema definition for 'template_design_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['template_design_category'] = array(
    'design_category_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'design_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'design_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'category_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
);