<?php
/**
 *
 * Schema definition for 'fanwall_approval'
 *
 * Last update: 2021-07-07
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['fanwall_approval'] = [
    'approval_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_FANWALL_APPROVAL_VID_AOV_VID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_FANWALL_APPROVAL_VID_AOV_VID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'post_id' => [
        'type' => 'int(11) unsigned',
    ],
];
