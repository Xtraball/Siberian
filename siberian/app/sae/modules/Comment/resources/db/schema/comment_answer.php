<?php
/**
 *
 * Schema definition for 'comment_answer'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['comment_answer'] = array(
    'answer_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'comment_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'comment',
            'column' => 'comment_id',
            'name' => 'comment_answer_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_COMMENT_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'customer_id' => array(
        'type' => 'int(11)',
        'index' => array(
            'key_name' => 'KEY_CUSTOMER_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'text' => array(
        'type' => 'varchar(2048)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'flag' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'is_visible' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
    'created_at_utc' => array(
        'type' => 'bigint'
    ),
    'updated_at_utc' => array(
        'type' => 'bigint'
    )
);