<?php
// force update
$module = new Installer_Model_Installer_Module();
$module->prepare('System', false);


/** Disable cron for Fresh sae install */
$disableCron = (!$module->isInstalled() && Siberian_Version::is('SAE')) ? '1' : '0';
$environment = 'production';

$configs = [
    [
        'code' => 'platform_name',
        'label' => 'Platform Name'
    ],
    [
        'code' => 'backoffice_theme',
        'label' => 'Backoffice Theme',
        'value' => ''
    ],
    [
        'code' => 'editor_design',
        'label' => "Editor's Design",
        'value' => 'flat',
    ],
    [
        'code' => 'company_name',
        'label' => 'Name'
    ],
    [
        'code' => 'company_phone',
        'label' => 'Phone'
    ],
    [
        'code' => 'company_address',
        'label' => 'Address'
    ],
    [
        'code' => 'company_country',
        'label' => 'Country'
    ],
    [
        'code' => 'company_vat_number',
        'label' => 'VAT Number'
    ],
    [
        'code' => 'system_territory',
        'label' => 'Timezone'
    ],
    [
        'code' => 'system_timezone',
        'label' => 'Timezone'
    ],
    [
        'code' => 'system_currency',
        'label' => 'Currency'
    ],
    [
        'code' => 'system_default_language',
        'label' => 'Default Languages'
    ],
    [
        'code' => 'app_default_identifier_android',
        'label' => 'Application base Package Name (Android)',
        'value' => ''
    ],
    [
        'code' => 'app_default_identifier_ios',
        'label' => 'Application base Bundle ID (iOS)',
        'value' => ''
    ],
    [
        'code' => 'system_publication_access_type',
        'label' => 'Publication access type',
        'value' => 'sources'
    ],
    [
        'code' => 'system_generate_apk',
        'label' => 'Users can generate APK/AAB',
        'value' => 'no'
    ],
    [
        'code' => 'support_email',
        'label' => 'Support Email Address'
    ],
    [
        'code' => 'support_link',
        'label' => 'Support Link'
    ],
    [
        'code' => 'support_name',
        'label' => 'Name'
    ],
    [
        'code' => 'support_chat_code',
        'label' => 'Online Chat'
    ],
    [
        'code' => 'logo',
        'label' => 'Logo'
    ],
    [
        'code' => 'favicon',
        'label' => 'Favicon'
    ],
    [
        'code' => 'logo_backoffice',
        'label' => 'Logo backoffice'
    ],
    [
        'code' => 'favicon_backoffice',
        'label' => 'Favicon backoffice'
    ],
    [
        'code' => 'application_try_apk',
        'label' => 'Try to generate the apk when downloading the Android source'
    ],
    [
        'code' => Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE,
        'label' => 'Default admin role'
    ],
    [
        'code' => 'application_ios_owner_admob_id',
        'label' => 'Admob ID for platform owner (Ios)'
    ],
    [
        'code' => 'application_ios_owner_admob_interstitial_id',
        'label' => 'Admob Interstitial ID for platform owner (Ios)'
    ],
    [
        'code' => 'application_ios_owner_admob_type',
        'label' => 'Admob type for platform owner (Ios)'
    ],
    [
        'code' => 'application_ios_owner_admob_weight',
        'label' => 'Admob split revenue for platform owner (Ios)',
        'value' => 100
    ],
    [
        'code' => 'application_android_owner_admob_id',
        'label' => 'Admob ID for platform owner (Android)'
    ],
    [
        'code' => 'application_android_owner_admob_interstitial_id',
        'label' => 'Admob Interstitial ID for platform owner (Android)'
    ],
    [
        'code' => 'application_android_owner_admob_type',
        'label' => 'Admob type for platform owner (Android)'
    ],
    [
        'code' => 'application_android_owner_admob_weight',
        'label' => 'Admob split revenue for platform owner (Android)',
        'value' => 100
    ],
    [
        'code' => 'application_owner_use_ads',
        'label' => 'Use ads for platform owner',
        'value' => '0'
    ],
    [
        'code' => 'disable_cron',
        'label' => 'Disable cron jobs',
        'value' => $disableCron
    ],
    [
        'code' => 'environment',
        'label' => 'Environment',
        'value' => $environment
    ],
    [
        'code' => 'update_channel',
        'label' => 'Update channel',
        'value' => 'stable'
    ],
    [
        'code' => 'siberiancms_key',
        'label' => 'Your CMS License Key',
        'value' => ''
    ],
    [
        'code' => 'cron_interval',
        'label' => 'CRON Scheduler interval',
        'value' => '60'
    ],
    [
        'code' => 'use_https',
        'label' => 'Use HTTPS',
        'value' => '1'
    ],
    [
        'code' => 'cpanel_type',
        'label' => 'Admin panel type',
        'value' => '-1'
    ],
    [
        'code' => 'letsencrypt_env',
        'label' => "Let's Encrypt environment",
        'value' => 'staging'
    ],
    [
        'code' => 'send_statistics',
        'label' => 'Send anonymous statistics to improve the apps builder.',
        'value' => '1'
    ],
    [
        'code' => 'campaign_is_active',
        'label' => '',
        'value' => '0'
    ],
    [
        'code' => 'global_quota',
        'label' => 'Disk usage quota in Mb',
        'value' => '10000'
    ],
    [
        'code' => 'enable_custom_smtp',
        'label' => 'Enable custom SMTP configuration',
        'value' => '0'
    ],
    [
        'code' => 'vat_check_activated',
        'label' => 'Vat check activated',
        'value' => '0'
    ],
    [
        'code' => 'invoice_footer_message',
        'label' => 'Invoices footer message',
        'value' => ''
    ],
    [
        'code' => 'session_handler',
        'label' => 'Session handler',
        'value' => 'mysql',
    ],
    [
        'code' => 'redis_endpoint',
        'label' => 'Redis endpoint',
        'value' => '',
    ],
    [
        'code' => 'redis_prefix',
        'label' => 'Redis prefix',
        'value' => 'PHPREDIS_SESSIONS:',
    ],
    [
        'code' => 'redis_auth',
        'label' => 'Redis AUTH KEY',
        'value' => '',
    ],
    [
        'code' => 'apk_build_type',
        'label' => 'APK Build type',
        'value' => 'release',
    ],
    [
        'code' => 'is_gdpr_enabled',
        'label' => 'Enable GDPR features & rules',
        'value' => '0',
    ],
    [
        'code' => 'editor_apk_service',
        'label' => 'APK/AAB Generator for builds from the Editor (Local or Service)',
        'value' => 'external-service',
    ],
    [
        'code' => 'main_domain',
        'label' => 'Main siberian domain',
        'value' => '',
    ],
    [
        'code' => 'java_home',
        'label' => 'JAVA_HOME path',
        'value' => '',
    ],
    [
        'code' => 'java_options',
        'label' => 'JAVA Extended options',
        'value' => '-Xmx384m -Xms384m -XX:MaxPermSize=384m',
    ],
    [
        'code' => 'gradle_options',
        'label' => 'GRADLE extended options',
        'value' => '-Dorg.gradle.daemon=true',
    ],
    [
        'code' => 'import_enabled',
        'label' => 'Allow users to import features',
        'value' => '0',
    ],
    [
        'code' => 'export_enabled',
        'label' => 'Allow users to export features',
        'value' => '0',
    ],
    [
        'code' => 'waf_enabled',
        'label' => 'Web Application Firewall',
        'value' => '1',
    ],
    [
        'code' => 'session_lifetime',
        'label' => 'Session lifetime in seconds',
        'value' => '604800',
    ],
    [
        'code' => 'privacy_policy',
        'label' => 'Default privacy policy',
        'value' => file_get_contents(__DIR__ . '/privacy-policy.html')
    ],
    [
        'code' => 'privacy_policy_gdpr',
        'label' => 'Default privacy policy section for GDPR',
        'value' => file_get_contents(__DIR__ . '/privacy-policy-gdpr.html'),
    ]
];

foreach ($configs as $index => $data) {
    $config = new System_Model_Config();
    $config
        ->setData($data)
        ->insertOnce(['code']);
}

// Find session_lifetime
$sConfig = (new System_Model_Config())->find('session_lifetime', 'code');
if ($sConfig &&
    $sConfig->getId() &&
    $sConfig->getLabel() === '') {
    $sConfig
        ->setLabel('Session lifetime in seconds')
        ->save();
}

// For version 4.18.17, we enforce the external apk service for everyone!
$enforceApkService = __get('enforce_apk_service');
if ($enforceApkService !== 'done') {
    __set('editor_apk_service', 'external-service');
    __set('enforce_apk_service', 'done');
}
