<?php
/**
 *
 * Schema definition for 'ssl_certificates'
 * // cert.pem  chain.pem  fullchain.pem  last.csr  private.pem  public.pem
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['ssl_certificates'] = array(
    'certificate_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'hostname' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'unique' => true,
    ),
    'certificate' => array(
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'chain' => array(
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'fullchain' => array(
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'last' => array(
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'private' => array(
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'public' => array(
        'type' => 'varchar(1024)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'domains' => array(
        'type' => 'longtext',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'environment' => array(
        'type' => 'varchar(64)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'source' => array(
        'type' => 'enum(\'customer\',\'letsencrypt\')',
    ),
    'status' => array(
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'enabled'
    ),
    'error_count' => array(
        'type' => 'tinyint(4)',
        'default' => '0'
    ),
    'error_log' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'error_date' => array(
        'type' => 'datetime',
    ),
    'renew_date' => array(
        'type' => 'datetime',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);