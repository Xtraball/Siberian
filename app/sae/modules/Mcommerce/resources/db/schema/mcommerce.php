<?php
/**
 *
 * Schema definition for 'mcommerce'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['mcommerce'] = array(
    'mcommerce_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'catalog_value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'mcommerce_ibfk_1',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'KEY_VALUE_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'root_category_id' => array(
        'type' => 'int(11) unsigned',
        'index' => array(
            'key_name' => 'KEY_CATEGORY_ID',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'name' => array(
        'type' => 'varchar(50)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'description' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'show_search' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'phone' => array(
        'type' => 'enum(\'hidden\',\'optional\',\'mandatory\')',
        'default' => 'mandatory',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'birthday' => array(
        'type' => 'enum(\'hidden\',\'optional\',\'mandatory\')',
        'default' => 'mandatory',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'invoicing_address' => array(
        'type' => 'enum(\'hidden\',\'optional\',\'mandatory\')',
        'default' => 'mandatory',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'delivery_address' => array(
        'type' => 'enum(\'hidden\',\'optional\',\'mandatory\')',
        'default' => 'mandatory',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);