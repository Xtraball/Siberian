<?php
/**
 *
 * Schema definition for 'topic_category_message'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['topic_category_message'] = array(
    'category_message_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'category_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'message_id' => array(
        'type' => 'int(11) unsigned',
    ),
);