<?php
/**
 *
 * Schema definition for 'application'
 *
 * Last update: 2016-04-28
 *
 */
$schemas = $schemas ?? [];
$schemas['application'] = [
    'app_id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'admin_id' => [
        'type' => 'int(11) unsigned',
    ],
    'layout_id' => [
        'type' => 'int(11) unsigned',
    ],
    'layout_visibility' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'homepage',
    ],
    'layout_options' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'design_id' => [
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ],
    'back_button' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'ion-ios-arrow-back',
    ],
    'back_button_class' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'left_toggle_class' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'right_toggle_class' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'bundle_id' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'package_name' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'key' => [
        'type' => 'varchar(20)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'unique' => true,
        'index' => [
            'key_name' => 'UNIQUE_APPLICATION_KEY',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => true,
        ],
    ],
    'design_code' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'ionic',
    ],
    'locale' => [
        'type' => 'varchar(15)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'currency' => [
        'type' => 'varchar(10)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'USD'
    ],
    'tabbar_account_name' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'tabbar_more_name' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'account_icon_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'account_subtitle' => [
        'type' => 'varchar(512)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'more_icon_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'more_subtitle' => [
        'type' => 'varchar(512)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'country_code' => [
        'type' => 'varchar(5)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'name' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'description' => [
        'type' => 'longtext',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'keywords' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'main_category_id' => [
        'type' => 'tinyint(1) unsigned',
        'is_null' => true,
    ],
    'secondary_category_id' => [
        'type' => 'tinyint(1) unsigned',
        'is_null' => true,
    ],
    'font_family' => [
        'type' => 'varchar(30)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image_hd' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image_tablet' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image_landscape' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image_landscape_hd' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image_landscape_tablet' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'background_image_unified' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'use_homepage_background_image_in_subpages' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'use_landscape' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'generate_scss' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'ios_status_bar_is_hidden' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ],
    'android_status_bar_is_hidden' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ],
    'logo' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'icon' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'android_push_icon' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'android_push_color' => [
        'type' => 'varchar(255)',
        'is_null' => false,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '#0099c7'
    ],
    'android_push_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'splash_version' => [
        'type' => 'tinyint(4)',
        'default' => 1,
    ],
    'startup_image' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'startup_image_retina' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'startup_image_iphone_6' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'startup_image_iphone_6_plus' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'startup_image_ipad_retina' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'startup_image_iphone_x' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'startup_image_unified' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'homepage_slider_is_visible' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ],
    'homepage_slider_size' => [
        'type' => 'tinyint(4)',
        'is_null' => true,
    ],
    'homepage_slider_opacity' => [
        'type' => 'tinyint(4)',
        'default' => 100,
    ],
    'homepage_slider_offset' => [
        'type' => 'tinyint(4)',
        'default' => 0,
    ],
    'homepage_slider_duration' => [
        'type' => 'int(11)',
        'is_null' => true,
        'default' => '3',
    ],
    'homepage_slider_loop_at_beginning' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ],
    'homepage_slider_library_id' => [
        'type' => 'int(11)',
        'is_null' => true,
    ],
    'custom_scss' => [
        'type' => 'MEDIUMTEXT',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'domain' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'subdomain' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'subdomain_is_validated' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
    ],
    'facebook_id' => [
        'type' => 'varchar(20)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'facebook_key' => [
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'facebook_token' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'facebook_linked_page' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'googlemaps_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'youtube_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'owm_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'ipinfo_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'flickr_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'flickr_secret' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_app_id' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_app_key_token' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_default_segment' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_android_app_id' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_android_app_key_token' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_ios_app_id' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'onesignal_ios_app_key_token' => [
        'type' => 'varchar(1024)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'twitter_consumer_key' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'twitter_consumer_secret' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'twitter_api_token' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'twitter_api_secret' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'instagram_client_id' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'instagram_token' => [
        'type' => 'varchar(100)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_active' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'pre_init' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'use_ads' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ],
    'test_ads' => [
        'type' => 'tinyint(1)',
        'is_null' => true,
        'default' => '0',
    ],
    'owner_use_ads' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'unlock_by' => [
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'account',
    ],
    'unlock_code' => [
        'type' => 'varchar(10)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'banner_title' => [
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'banner_author' => [
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'banner_button_label' => [
        'type' => 'varchar(150)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'require_to_be_logged_in' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'allow_all_customers_to_access_locked_features' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'offline_content' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '1',
    ],
    'disable_updates' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'privacy_policy' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'privacy_policy_title' => [
        'type' => 'varchar(255)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'privacy_policy_gdpr' => [
        'type' => 'text',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'is_locked' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'request_tracking_authorization' => [
        'type' => 'tinyint(1) unsigned',
        'default' => '0',
    ],
    'can_access_editor' => [
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'global',
    ],
    'free_until' => [
        'type' => 'datetime',
        'is_null' => true,
    ],
    'can_be_published' => [
        'type' => 'tinyint(1)',
        'default' => '1',
    ],
    'fidelity_rate' => [
        'type' => 'varchar(50)',
        'is_null' => true,
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '1'
    ],
    'disable_battery_optimization' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'disable_location' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'enable_custom_smtp' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'smtp_credentials' => [
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '',
    ],
    'mediation_facebook' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'mediation_startapp' => [
        'type' => 'tinyint(1)',
        'default' => '0',
    ],
    'size_on_disk' => [
        'type' => 'int(11) unsigned',
    ],
    'version' => [
        'type' => 'varchar(32)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => '0'
    ],
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ],
];
