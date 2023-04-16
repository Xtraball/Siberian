<?php
/**
 *
 * Schema definition for 'push2_player_message'
 *
 * Last update: 2023-01-09
 *
 */
$schemas = $schemas ?? [];
$schemas['push2_player_message'] = [
    'player_message_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'player_id' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'message_id' => [
        'type' => 'int(11) unsigned',
    ],
];
