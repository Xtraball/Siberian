<?php
$module = new Installer_Model_Installer_Module();
$module->prepare("System", false);

$editor_design = ($module->isInstalled()) ? "siberian" : "flat";
/** Disable cron for Fresh sae install */
$disable_cron = (!$module->isInstalled() && Siberian_Version::is("SAE")) ? "1" : "0";
$environment = (APPLICATION_ENV == "production") ? "production" : "development";

$configs = array(
    array(
        "code" => "platform_name",
        "label" => "Platform Name"
    ),
    array(
        "code" => "company_name",
        "label" => "Name"
    ),
    array(
        "code" => "company_phone",
        "label" => "Phone"
    ),
    array(
        "code" => "company_address",
        "label" => "Address"
    ),
    array(
        "code" => "company_country",
        "label" => "Country"
    ),
    array(
        "code" => "company_vat_number",
        "label" => "VAT Number"
    ),
    array(
        "code" => "system_territory",
        "label" => "Timezone"
    ),
    array(
        "code" => "system_timezone",
        "label" => "Timezone"
    ),
    array(
        "code" => "system_currency",
        "label" => "Currency"
    ),
    array(
        "code" => "system_default_language",
        "label" => "Default Languages"
    ),
    array(
        "code" => "system_publication_access_type",
        "label" => "Publication access type",
        "value" => "sources"
    ),
    array(
        "code" => "system_generate_apk",
        "label" => "Users can generate APK",
        "value" => "no"
    ),
    array(
        "code" => "support_email",
        "label" => "Support Email Address"
    ),
    array(
        "code" => "support_link",
        "label" => "Support Link"
    ),
    array(
        "code" => "support_name",
        "label" => "Name"
    ),
    array(
        "code" => "support_chat_code",
        "label" => "Online Chat"
    ),
    array(
        "code" => "logo",
        "label" => "Logo"
    ),
    array(
        "code" => "favicon",
        "label" => "Favicon"
    ),
    array(
        "code" => "application_try_apk",
        "label" => "Try to generate the apk when downloading the Android source"
    ),
    array(
        "code" => Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE,
        "label" => "Default admin role"
    ),
    array(
        "code" => "application_ios_owner_admob_id",
        "label" => "Admob ID for platform owner (Ios)"
    ),
    array(
        "code" => "application_ios_owner_admob_type",
        "label" => "Admob type for platform owner (Ios)"
    ),
    array(
        "code" => "application_android_owner_admob_id",
        "label" => "Admob ID for platform owner (Android)"
    ),
    array(
        "code" => "application_android_owner_admob_type",
        "label" => "Admob type for platform owner (Android)"
    ),
    array(
        "code" => "application_owner_use_ads",
        "label" => "Use ads for platform owner",
        "value" => "0"
    ),
    array(
        "code" => "disable_cron",
        "label" => "Disable cron jobs",
        "value" => $disable_cron
    ),
    array(
        "code" => "environment",
        "label" => "Environment",
        "value" => $environment
    ),
    array(
        "code" => "update_channel",
        "label" => "Update channel",
        "value" => "stable"
    ),
    array(
        "code" => "ios_autobuild_key",
        "label" => "Your iOS autobuild License Key",
        "value" => ""
    ),
    array(
        "code" => "cron_interval",
        "label" => "CRON Scheduler interval",
        "value" => "60"
    ),
    array(
        "code" => "use_https",
        "label" => "Use HTTPS",
        "value" => "0"
    ),
    array(
        "code" => "cpanel_type",
        "label" => "Admin panel type",
        "value" => "-1"
    ),
    array(
        "code" => "letsencrypt_env",
        "label" => "Let's Encrypt environment",
        "value" => "staging"
    ),
    array(
        "code" => "send_statistics",
        "label" => "Send anonymous statistics to improve the apps builder.",
        "value" => "1"
    ),
    array(
        "code" => "campaign_is_active",
        "label" => "",
        "value" => "1"
    )
);

foreach($configs as $data) {
    $config = new System_Model_Config();
    $config
        ->setData($data)
        ->insertOnce(array("code"));

}

# Installing design
$data = array(
    "code" => "editor_design",
    "label" => "Editor's Design",
    "value" => $editor_design,
);

$config = new System_Model_Config();
$config->find($data["code"], "code");

if(!$config->getId()) {
    $config->setData($data)->save();
}

# Installing app limit for analytics demo mode
$data = array(
    "code" => "analytic_app_limit",
    "label" => "App limit before real data mode in analytics",
    "value" => 500,
);

$config = new System_Model_Config();
$config->find($data["code"], "code");

if(!$config->getId()) {
    $config->setData($data)->save();
}