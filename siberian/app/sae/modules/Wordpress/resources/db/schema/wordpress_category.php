<?php
/**
 *
 * Schema definition for 'wordpress_category'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['wordpress_category'] = array(
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'wp_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'wordpress',
            'column' => 'wp_id',
            'name' => 'wordpress_category_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_WP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'wp_category_id' => array(
        'type' => 'int(11)',
    ),
    'position' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
);