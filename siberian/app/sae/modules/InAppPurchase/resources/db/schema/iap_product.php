<?php
/**
 *
 * Schema definition for 'iap_product'
 *
 * Last update: 2020-07-15
 *
 */
$schemas = $schemas ?? [];
$schemas['iap_product'] = [
    'product_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application',
            'column' => 'app_id',
            'name' => 'IAPPRODUCT_APPID_APP_APPID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'IAPPRODUCT_APPID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ],
    ],
    'stores' => [ /** GOOGLE,APPLE,BOTH */
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'BOTH',
    ],
    /**
     * store.FREE_SUBSCRIPTION         = "free subscription";
     * store.PAID_SUBSCRIPTION         = "paid subscription";
     * store.NON_RENEWING_SUBSCRIPTION = "non renewing subscription";
     * store.CONSUMABLE                = "consumable";
     * store.NON_CONSUMABLE            = "non consumable";
     */
    'type' => [ /** FREE_SUBSCRIPTION, PAID_SUBSCRIPTION, NON_RENEWING_SUBSCRIPTION, CONSUMABLE, NON_CONSUMABLE */
        'type' => 'varchar(64)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'NON_CONSUMABLE',
    ],
    'google_id' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'apple_id' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'is_null' => true,
    ],
    'alias' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
