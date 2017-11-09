<?php
/**
 *
 * Schema definition for 'topic_subscription'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['topic_subscription'] = array(
    'subscription_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'category_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'topic_category',
            'column' => 'category_id',
            'name' => 'FK_TOPIC_SUBSCRIPTION_CATEGORY_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'category_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'device_uid' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
);