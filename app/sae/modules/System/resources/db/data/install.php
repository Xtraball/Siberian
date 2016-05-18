<?php
$data = array(
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
        "value" => in_array(Siberian_Version::TYPE, array("MAE", "SAE")) ? "sources" : "info"
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
    )
);

if(Siberian_Version::TYPE == "PE") {
    $data = array_merge($data, array(
        array(
            "code" => "sales_email",
            "label" => "Sales Email Address"
        ),
        array(
            "code" => "sales_name",
            "label" => "Name"
        ),
        array(
            "code" => "sales_tc",
            "label" => "Terms & Conditions",
            "value" => null
        ),
        array(
            "code" => "application_free_trial",
            "label" => "Free trial period"
        )
    ));
}

foreach($data as $configData) {
    $config = new System_Model_Config();
    $config->find($configData["code"], "code")
        ->setData($configData)
        ->save()
    ;
}


$configs = array(
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
    )
);

foreach($configs as $data) {

    $config = new System_Model_Config();
    $config->find($data["code"], "code");

    if(!$config->getId()) {
        $config->setData($data)->save();
    }
}

/** @todo ... check usage */
$this->query("
    DELETE FROM `system_config` WHERE code = '' OR code IS NULL
");