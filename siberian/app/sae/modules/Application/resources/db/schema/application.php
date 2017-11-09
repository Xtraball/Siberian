<?php
/**
 *
 * Schema definition for 'application'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['application'] = array(
    'app_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'admin_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'layout_id' => array(
        'type' => 'int(11) unsigned',
    ),
    'layout_visibility' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'homepage',
    ),
    'layout_options' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'design_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'bundle_id' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'package_name' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'key' => array(
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'unique' => true,
        'index' => array(
            'key_name' => 'UNIQUE_APPLICATION_KEY',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ),
    ),
    'design_code' => array(
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'ionic',
    ),
    'locale' => array(
        'type' => 'varchar(6)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'tabbar_account_name' => array(
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'tabbar_more_name' => array(
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'account_icon_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'account_subtitle' => array(
        'type' => 'varchar(512)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'more_icon_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'more_subtitle' => array(
        'type' => 'varchar(512)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'country_code' => array(
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'name' => array(
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'description' => array(
        'type' => 'longtext',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'keywords' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'main_category_id' => array(
        'type' => 'tinyint(1) unsigned',
        'is_null' => true,
    ),
    'secondary_category_id' => array(
        'type' => 'tinyint(1) unsigned',
        'is_null' => true,
    ),
    'font_family' => array(
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image_hd' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image_tablet' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image_landscape' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image_landscape_hd' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'background_image_landscape_tablet' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'use_homepage_background_image_in_subpages' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'use_landscape' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'ios_status_bar_is_hidden' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ),
    'android_status_bar_is_hidden' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ),
    'logo' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'icon' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'android_push_icon' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'android_push_color' => array(
        'type' => 'varchar(255)',
        'is_null' => false,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '#0099c7'
    ),
    'android_push_image' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'startup_image' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'startup_image_retina' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'startup_image_iphone_6' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'startup_image_iphone_6_plus' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'startup_image_ipad_retina' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'homepage_slider_is_visible' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ),
    'homepage_slider_size' => array(
        'type' => 'tinyint(4)',
        'is_null' => true,
    ),
    'homepage_slider_opacity' => array(
        'type' => 'tinyint(4)',
        'default' => 100,
    ),
    'homepage_slider_offset' => array(
        'type' => 'tinyint(4)',
        'default' => 0,
    ),
    'homepage_slider_duration' => array(
        'type' => 'int(11)',
        'is_null' => true,
        'default' => '3',
    ),
    'homepage_slider_loop_at_beginning' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ),
    'homepage_slider_library_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
    ),
    'custom_scss' => array(
        'type' => 'MEDIUMTEXT',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'domain' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'UNIQUE_KEY_SUBDOMAIN_DOMAIN',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => true,
        ),
    ),
    'subdomain' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'index' => array(
            'key_name' => 'UNIQUE_KEY_SUBDOMAIN_DOMAIN',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => true,
        ),
    ),
    'subdomain_is_validated' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
    ),
    'facebook_id' => array(
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'facebook_key' => array(
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'facebook_token' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'facebook_linked_page' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'googlemaps_key' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'flickr_key' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'flickr_secret' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'twitter_consumer_key' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'twitter_consumer_secret' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'twitter_api_token' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'twitter_api_secret' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'instagram_client_id' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'instagram_token' => array(
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_active' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'use_ads' => array(
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ),
    'owner_use_ads' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'unlock_by' => array(
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'account',
    ),
    'unlock_code' => array(
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'banner_title' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'banner_author' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'banner_button_label' => array(
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'require_to_be_logged_in' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'allow_all_customers_to_access_locked_features' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'offline_content' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ),
    'privacy_policy' => array(
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'privacy_policy_title' => array(
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_locked' => array(
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ),
    'free_until' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'can_be_published' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'fidelity_rate' => array(
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '1'
    ),
    'enable_custom_smtp' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
    'smtp_credentials' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);