<?php
/**
 *
 * Schema definition for 'loyalty_card_qr_token'
 *
 * One-time QRCodes of the loyalty card (#851): only the hash of the token is stored.
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['loyalty_card_qr_token'] = array(
    'token_id' => array(
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ),
    'password_id' => array(
        'type' => 'int(11)',
        'foreign_key' => array(
            'table' => 'loyalty_card_password',
            'column' => 'password_id',
            'name' => 'loyalty_card_qr_token_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_PASSWORD_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'token_hash' => array(
        'type' => 'varchar(64)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'UNIQUE_TOKEN_HASH',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ),
    ),
    'expires_at' => array(
        'type' => 'datetime',
    ),
    'used_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
);
