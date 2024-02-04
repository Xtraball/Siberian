<?php
/**
 *
 * Schema definition for 'push2_message'
 *
 * Last update: 2023-01-14
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['push2_message'] = [
    'message_id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'FK_PUSH2_MESSAGE_APP_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PUSH2_MESSAGE_APP_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'segment' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'title' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'subtitle' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'body' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'big_picture' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'big_picture_url' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'use_location' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'latitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'longitude' => [
        'type' => 'decimal(11,8)',
        'is_null' => true,
    ],
    'radius' => [
        'type' => 'decimal(7,2)',
        'is_null' => true,
    ],
    'open_feature' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'feature_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'open_url' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'feature_url' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'is_silent' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'send_after' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        // "Thu Sep 24 2015 14:00:00 GMT-0700 (PDT)"
        // "September 24th 2015, 2:00:00 pm UTC-07:00"
        // "2015-09-24 14:00:00 GMT-0700"
        // "Sept 24 2015 14:00:00 GMT-0700"
    ],
    'delayed_option' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        // Examples: "timezone" works with delivery_time_of_day
    ],
    'delivery_time_of_day' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        /** Examples: "9:00AM" "21:45" "9:45:30" */
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_PUSH2_MESSAGES_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IDX_PUSH2_MESSAGES_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'status' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'queued',
    ],
    'onesignal_id' => [
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'external_id' => [
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'is_individual' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_test' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'is_for_module' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'recipients' => [
        'type' => 'int(11) unsigned',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
