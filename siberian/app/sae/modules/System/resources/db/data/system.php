<?php
// force update
$module = new Installer_Model_Installer_Module();
$module->prepare("System", false);

/** Force flat. */
$editor_design = "flat";

/** Disable cron for Fresh sae install */
$disable_cron = (!$module->isInstalled() && Siberian_Version::is("SAE")) ? "1" : "0";
$environment = "production";

$configs = [
    [
        "code" => "platform_name",
        "label" => "Platform Name"
    ],
    [
        "code" => "backoffice_theme",
        "label" => "Backoffice Theme",
        "value" => ""
    ],
    [
        "code" => "company_name",
        "label" => "Name"
    ],
    [
        "code" => "company_phone",
        "label" => "Phone"
    ],
    [
        "code" => "company_address",
        "label" => "Address"
    ],
    [
        "code" => "company_country",
        "label" => "Country"
    ],
    [
        "code" => "company_vat_number",
        "label" => "VAT Number"
    ],
    [
        "code" => "system_territory",
        "label" => "Timezone"
    ],
    [
        "code" => "system_timezone",
        "label" => "Timezone"
    ],
    [
        "code" => "system_currency",
        "label" => "Currency"
    ],
    [
        "code" => "system_default_language",
        "label" => "Default Languages"
    ],
    [
        "code" => "app_default_identifier_android",
        "label" => "Application base Package Name (Android)",
        "value" => ""
    ],
    [
        "code" => "app_default_identifier_ios",
        "label" => "Application base Bundle ID (iOS)",
        "value" => ""
    ],
    [
        "code" => "system_publication_access_type",
        "label" => "Publication access type",
        "value" => "sources"
    ],
    [
        "code" => "system_generate_apk",
        "label" => "Users can generate APK",
        "value" => "no"
    ],
    [
        "code" => "support_email",
        "label" => "Support Email Address"
    ],
    [
        "code" => "support_link",
        "label" => "Support Link"
    ],
    [
        "code" => "support_name",
        "label" => "Name"
    ],
    [
        "code" => "support_chat_code",
        "label" => "Online Chat"
    ],
    [
        "code" => "logo",
        "label" => "Logo"
    ],
    [
        "code" => "favicon",
        "label" => "Favicon"
    ],
    [
        "code" => "logo_backoffice",
        "label" => "Logo backoffice"
    ],
    [
        "code" => "favicon_backoffice",
        "label" => "Favicon backoffice"
    ],
    [
        "code" => "application_try_apk",
        "label" => "Try to generate the apk when downloading the Android source"
    ],
    [
        "code" => Acl_Model_Role::DEFAULT_ADMIN_ROLE_CODE,
        "label" => "Default admin role"
    ],
    [
        "code" => "application_ios_owner_admob_id",
        "label" => "Admob ID for platform owner (Ios)"
    ],
    [
        "code" => "application_ios_owner_admob_interstitial_id",
        "label" => "Admob Interstitial ID for platform owner (Ios)"
    ],
    [
        "code" => "application_ios_owner_admob_type",
        "label" => "Admob type for platform owner (Ios)"
    ],
    [
        "code" => "application_ios_owner_admob_weight",
        "label" => "Admob split revenue for platform owner (Ios)",
        "value" => 100
    ],
    [
        "code" => "application_android_owner_admob_id",
        "label" => "Admob ID for platform owner (Android)"
    ],
    [
        "code" => "application_android_owner_admob_interstitial_id",
        "label" => "Admob Interstitial ID for platform owner (Android)"
    ],
    [
        "code" => "application_android_owner_admob_type",
        "label" => "Admob type for platform owner (Android)"
    ],
    [
        "code" => "application_android_owner_admob_weight",
        "label" => "Admob split revenue for platform owner (Android)",
        "value" => 100
    ],
    [
        "code" => "application_owner_use_ads",
        "label" => "Use ads for platform owner",
        "value" => "0"
    ],
    [
        "code" => "disable_cron",
        "label" => "Disable cron jobs",
        "value" => $disable_cron
    ],
    [
        "code" => "environment",
        "label" => "Environment",
        "value" => $environment
    ],
    [
        "code" => "update_channel",
        "label" => "Update channel",
        "value" => "stable"
    ],
    [
        "code" => "ios_autobuild_key",
        "label" => "Your iOS autobuild License Key",
        "value" => ""
    ],
    [
        "code" => "siberiancms_key",
        "label" => "Your CMS License Key",
        "value" => ""
    ],
    [
        "code" => "cron_interval",
        "label" => "CRON Scheduler interval",
        "value" => "60"
    ],
    [
        "code" => "use_https",
        "label" => "Use HTTPS",
        "value" => "1"
    ],
    [
        "code" => "cpanel_type",
        "label" => "Admin panel type",
        "value" => "-1"
    ],
    [
        "code" => "letsencrypt_env",
        "label" => "Let's Encrypt environment",
        "value" => "staging"
    ],
    [
        "code" => "send_statistics",
        "label" => "Send anonymous statistics to improve the apps builder.",
        "value" => "1"
    ],
    [
        "code" => "campaign_is_active",
        "label" => "",
        "value" => "0"
    ],
    [
        "code" => "global_quota",
        "label" => "Disk usage quota in Mb",
        "value" => "10000"
    ],
    [
        "code" => "enable_custom_smtp",
        "label" => "Enable custom SMTP configuration",
        "value" => "0"
    ],
    [
        "code" => "vat_check_activated",
        "label" => "Vat check activated",
        "value" => "0"
    ],
    [
        "code" => "invoice_footer_message",
        "label" => "Invoices footer message",
        "value" => ""
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
        'label' => 'APK Generator for builds from the Editor (Local or Service)',
        'value' => 'local-service',
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
];

$privacyPolicy = '<h1>Privacy Policy of the #APP_NAME application</h1>
<p>This Application collects some Personal Data from its Users.</p>
<h2>Data Controller and Owner</h2>
<p>&nbsp;</p>
<h2>Types of Data collected</h2>
<p>Among the types of Personal Data that this Application collects, by itself or through third parties, there are: Geographic position, Cookie and Usage Data. Other Personal Data collected may be described in other sections of this privacy policy or by dedicated explanation text contextually with the Data collection. The Personal Data may be freely provided by the User, or collected automatically when using this Application. Any use of Cookies - or of other tracking tools - by this Application or by the owners of third party services used by this Application, unless stated otherwise, serves to identify Users and remember their preferences, for the sole purpose of providing the service required by the User. Failure to provide certain Personal Data may make it impossible for this Application to provide its services. The User assumes responsibility for the Personal Data of third parties published or shared through this Application and declares to have the right to communicate or broadcast them, thus relieving the Data Controller of all responsibility.</p>
<h2>Mode and place of processing the Data</h2>
<h3>Methods of processing</h3>
<p>The Data Controller processes the Data of Users in a proper manner and shall take appropriate security measures to prevent unauthorized access, disclosure, modification, or unauthorized destruction of the Data. The Data processing is carried out using computers and/or IT enabled tools, following organizational procedures and modes strictly related to the purposes indicated. In addition to the Data Controller, in some cases, the Data may be accessible to certain types of persons in charge, involved with the operation of the site (administration, sales, marketing, legal, system administration) or external parties (such as third party technical service providers, mail carriers, hosting providers, IT companies, communications agencies) appointed, if necessary, as Data Processors by the Owner. The updated list of these parties may be requested from the Data Controller at any time.</p>
<h3>Place</h3>
<p>The Data is processed at the Data Controller&#39;s operating offices and in any other places where the parties involved with the processing are located. For further information, please contact the Data Controller.</p>
<h3>Retention time</h3>
<p>The Data is kept for the time necessary to provide the service requested by the User, or stated by the purposes outlined in this document, and the User can always request that the Data Controller suspend or remove the data.</p>
<h2>The use of the collected Data</h2>
<p>The Data concerning the User is collected to allow the Application to provide its services, as well as for the following purposes: Access to third party services&#39; accounts, Location-based interactions, Content commenting and Interaction with external social networks and platforms. The Personal Data used for each purpose is outlined in the specific sections of this document.</p>
<h2>Facebook permissions asked by this Application</h2>
<p>This Application may ask some Facebook permissions allowing it to perform actions with the User&#39;s Facebook account and to retrieve information, including Personal Data, from it. For more information about the following permissions, refer to the Facebook permissions documentation and to the Facebook privacy policy. The permissions asked are the following:</p>
<h3>Basic information</h3>
<p>By default, this includes certain User&rsquo;s Data such as id, name, picture, gender, and their locale. Certain connections of the User, such as the Friends, are also available. If the user has made more of their data public, more information will be available.</p>
<h3>Checkins</h3>
<p>Provides read access to the authorized user&#39;s check-ins</p>
<h3>Email</h3>
<p>Provides access to the user&#39;s primary email address</p>
<h3>Likes</h3>
<p>Provides access to the list of all of the pages the user has liked.</p>
<h3>Photos</h3>
<p>Provides access to the photos the user has uploaded, and photos the user has been tagged in.</p>
<h3>Publish App Activity</h3>
<p>Allows the app to publish to the Open Graph using Built-in Actions, Achievements, Scores, or Custom Actions. The app can also publish other activity which is detailed in the Facebook&#39;s Publishing Permissions document.</p>
<h2>Detailed information on the processing of Personal Data</h2>
<p>Personal Data is collected for the following purposes and using the following services:</p>
<h3>Access to third party services&#39; accounts</h3>
<p>These services allow this Application to access Data from your account on a third party service and perform actions with it. These services are not activated automatically, but require explicit authorization by the User.<br />
<strong>Access to the Facebook account (This Application)</strong><br />
This service allows this Application to connect with the User&#39;s account on the Facebook social network, provided by Facebook Inc. Permissions asked: Checkins, Email, Likes, Photos and Publish App Activity. Place of processing : USA &ndash; Privacy Policy</p>
<h3>Content commenting</h3>
<p>Content commenting services allow Users to make and publish their comments on the contents of this Application. Depending on the settings chosen by the Owner, Users may also leave anonymous comments. If there is an email address among the Personal Data provided by the User, it may be used to send notifications of comments on the same content. Users are responsible for the content of their own comments. If a content commenting service provided by third parties is installed, it may still collect web traffic data for the pages where the comment service is installed, even when users do not use the content commenting service.<br />
<strong>Facebook Comments (Facebook)</strong><br />
Facebook Comments is a content commenting service provided by Facebook Inc. enabling the User to leave comments and share them on the Facebook platform. Personal Data collected: Cookie and Usage Data. Place of processing : USA &ndash; Privacy Policy</p>
<h3>Interaction with external social networks and platforms</h3>
<p>These services allow interaction with social networks or other external platforms directly from the pages of this Application. The interaction and information obtained by this Application are always subject to the User&rsquo;s privacy settings for each social network. If a service enabling interaction with social networks is installed it may still collect traffic data for the pages where the service is installed, even when Users do not use it.<br />
<strong>Facebook Like button and social widgets (Facebook)</strong><br />
The Facebook Like button and social widgets are services allowing interaction with the Facebook social network provided by Facebook Inc. Personal Data collected: Cookie and Usage Data. Place of processing : USA &ndash; Privacy Policy</p>
<h3>Location-based interactions</h3>
<p><strong>Geolocation (This Application)&nbsp;</strong><br />
This Application may collect, use, and share User location Data in order to provide location-based services. Most browsers and devices provide tools to opt out from this feature by default. If explicit authorization has been provided, the User&rsquo;s location data may be tracked by this Application. Personal Data collected: Geographic position.</p>
<h2>Additional information about Data collection and processing</h2>
<h3>Legal Action</h3>
<p>The User&#39;s Personal Data may be used for legal purposes by the Data Controller, in Court or in the stages leading to possible legal action arising from improper use of this Application or the related services.<br />
The User is aware of the fact that the Data Controller may be required to reveal personal data upon request of public authorities.</p>
<h3>Additional information about User&#39;s Personal Data</h3>
<p>In addition to the information contained in this privacy policy, this Application may provide the User with additional and contextual information concerning particular services or the collection and processing of Personal Data upon request.</p>
<h3>System Logs and Maintenance</h3>
<p>For operation and maintenance purposes, this Application and any third party services may collect files that record interaction with this Application (System Logs) or use for this purpose other Personal Data (such as IP Address).</p>
<h3>Information not contained in this policy</h3>
<p>More details concerning the collection or processing of Personal Data may be requested from the Data Controller at any time. Please see the contact information at the beginning of this document.</p>
<h3>The rights of Users</h3>
<p>Users have the right, at any time, to know whether their Personal Data has been stored and can consult the Data Controller to learn about their contents and origin, to verify their accuracy or to ask for them to be supplemented, cancelled, updated or corrected, or for their transformation into anonymous format or to block any data held in violation of the law, as well as to oppose their treatment for any and all legitimate reasons. Requests should be sent to the Data Controller at the contact information set out above.<br />
This Application does not support &ldquo;Do Not Track&rdquo; requests.<br />
To determine whether any of the third party services it uses honor the &ldquo;Do Not Track&rdquo; requests, please read their privacy policies.</p>
<h3>Changes to this privacy policy</h3>
<p>The Data Controller reserves the right to make changes to this privacy policy at any time by giving notice to its Users on this page. It is strongly recommended to check this page often, referring to the date of the last modification listed at the bottom. If a User objects to any of the changes to the Policy, the User must cease using this Application and can request that the Data Controller erase the Personal Data. Unless stated otherwise, the then-current privacy policy applies to all Personal Data the Data Controller has about Users.</p>
<h2>Definitions and legal references</h2>
<h3>Personal Data (or Data)</h3>
<p>Any information regarding a natural person, a legal person, an institution or an association, which is, or can be, identified, even indirectly, by reference to any other information, including a personal identification number.</p>
<h3>Usage Data</h3>
<p>Information collected automatically from this Application (or third party services employed in this Application ), which can include: the IP addresses or domain names of the computers utilized by the Users who use this Application, the URI addresses (Uniform Resource Identifier), the time of the request, the method utilized to submit the request to the server, the size of the file received in response, the numerical code indicating the status of the server&#39;s answer (successful outcome, error, etc.), the country of origin, the features of the browser and the operating system utilized by the User, the various time details per visit (e.g., the time spent on each page within the Application) and the details about the path followed within the Application with special reference to the sequence of pages visited, and other parameters about the device operating system and/or the User&#39;s IT environment.</p>
<h3>User</h3>
<p>The individual using this Application, which must coincide with or be authorized by the Data Subject, to whom the Personal Data refer.</p>
<h3>Data Subject</h3>
<p>The legal or natural person to whom the Personal Data refers to.</p>
<h3>Data Processor (or Data Supervisor)</h3>
<p>The natural person, legal person, public administration or any other body, association or organization authorized by the Data Controller to process the Personal Data in compliance with this privacy policy.</p>
<h3>Data Controller (or Owner)</h3>
<p>The natural person, legal person, public administration or any other body, association or organization with the right, also jointly with another Data Controller, to make decisions regarding the purposes, and the methods of processing of Personal Data and the means used, including the security measures concerning the operation and use of this Application. The Data Controller, unless otherwise specified, is the Owner of this Application.</p>
<h3>This Application</h3>
<p>The hardware or software tool by which the Personal Data of the User is collected.</p>
<h3>Cookie</h3>
<p>Small piece of data stored in the User&#39;s device.</p>
<h3>Legal information</h3>
<p>Notice to European Users: this privacy statement has been prepared in fulfillment of the obligations under Art. 10 of EC Directive n. 95/46/EC, and under the provisions of Directive 2002/58/EC, as revised by Directive 2009/136/EC, on the subject of Cookies. This privacy policy relates solely to this Application.</p>
<p>&nbsp;</p>
<p><em><strong>Latest update: April 24, 2018.</strong></em></p>';

$privacyPolicyGdpr = '<h1>Extended&nbsp;policy&nbsp;concerning the application of the European GDPR.</h1>
<p><strong>In accordance with the obligations of the</strong>&nbsp;<em><span style="color:rgb(68, 68, 68); font-family:lucida grande,lucida sans unicode,lucida sans,arial,sans-serif; font-size:12.8px">Regulation (EU) 2016/679 of the European Parliament and of the Council of 27 April 2016 on the protection of natural persons with regard to the processing of personal data and on the free movement of such data, and repealing Directive 95/46/EC (General Data Protection Regulation) (Text with EEA relevance)</span></em></p>
<h1>Data processor</h1>
<p>#COMPANY.NAME# does not own any of the client data stored or processed via the service #PLATFORM.NAME#.</p>
<p>#COMPANY.NAME# is not responsible for the content of the personal data contained in the client data or other information stored on its servers.</p>
<p>At the discretion of the client or user nor is #COMPANY.NAME# responsible for the manner in which the client or user collects, handles disclosure, distributes or otherwise processes such information.</p>
<h1>Contact to access, correct, delete any data information</h1>
<p>#CONTACT.FULL#</p>
<h1>Your choices</h1>
<h2>Access, Correction, Deletion&nbsp;</h2>
<p>We respect your privacy rights and provide you with reasonable access to the Personal Data that you may have provided through your use of the Services.</p>
<p>If you wish to access or amend any other Personal Data we hold about you or to request that we delete any information about you that we have obtained from an Integrated Service, you may contact us.</p>
<p>At your request we will have any reference to you deleted or blocked in our database.</p>
<p>You may update, correct, or delete your Account information and preferences at any time by accessing your Account settings page on the Service.<br />
Please note that while any changes you make will be reflected in active user databases instantly or within a reasonable period of time, we may retain all information you submit for backups, archiving, prevention of fraud and abuse, analytic, satisfaction of legal obligations, or where we otherwise reasonably believe that we have a legitimate reason to do so<br />
You may decline to share certain personal data with us, in which case we may not be able to provide to you some of the features and functionality of the Service.<br />
At any time, you may object to the processing of your personal data, on legitimate grounds except if otherwise permitted by applicable law.</p>
<h1>Intended use of personal data</h1>
<p>Unless you want to use advanced features in applications, we do not require any form of registration, allowing you to use the application without telling us who you are.</p>
<p>However some services do require you to provide us with personal data.</p>
<p>In these situations, if you choose to withhold any personal data request by us, it may not not be possible for you to gain access to certain parts of the application and for us to respond to your query.</p>
<h1>How we use the information we collect</h1>
<p>We use the information that we collect in a variety of ways in providing the service and operating our business.</p>
<p>Including the following operations:<br />
- Maintain, enhance and provide all features of the service, to provide the services and information that you request,<br />
to respond to comments and questions and to provide support to users of the service we process client data solely in accordance with the directions provided by the applicable client or user improvements.<br />
- We may use a visitor or user email address or other information <strong>other than client data</strong>&nbsp;to contact that visitor or user for administrative purposes such as customer service,<br />
to address intellectual property infringement, right of privacy violations or defamation issues related to the client data or personal data posted on the service.</p>
<p>&nbsp;</p>
<p><em><strong>Latest update: April 24, 2018.</strong></em></p>';

$configs[] = [
    'code' => 'privacy_policy',
    'label' => 'Default privacy policy',
    'value' => $privacyPolicy
];

$configs[] = [
    'code' => 'privacy_policy_gdpr',
    'label' => 'Default privacy policy section for GDPR',
    'value' => $privacyPolicyGdpr,
];

foreach ($configs as $index => $data) {
    $config = new System_Model_Config();
    $config
        ->setData($data)
        ->insertOnce(['code']);
}

# Installing design
$data = [
    "code" => "editor_design",
    "label" => "Editor's Design",
    "value" => $editor_design,
];

$config = new System_Model_Config();
$config->find($data["code"], "code");

if(!$config->getId()) {
    $config->setData($data)->save();
}

# Installing app limit for analytics demo mode
$data = [
    "code" => "analytic_app_limit",
    "label" => "App limit before real data mode in analytics",
    "value" => 500,
];

$config = new System_Model_Config();
$config->find($data["code"], "code");

if (!$config->getId()) {
    $config->setData($data)->save();
}

# 4.8.7: Maintenance, remove blank entries
$this->query("DELETE FROM `system_config` WHERE code = '';");

# 4.10.0: Clear locks
Siberian_Cache::__clearLocks();

# 4.12.0
$id_android = __get("app_default_identifier_android");
$id_ios = __get("app_default_identifier_ios");

$buildId = function ($suffix) {
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $url = mb_strtolower($request->getServer('HTTP_HOST'));
    $url = array_reverse(explode(".", $url));
    $url[] = $suffix;

    foreach ($url as &$part) {
        $part = preg_replace("/[^0-9a-z\.]/i", "", $part);
    }

    return implode(".", $url);
};

if (empty($id_android)) {
    __set("app_default_identifier_android", $buildId("android"));
}

if (empty($id_ios)) {
    __set("app_default_identifier_ios", $buildId("ios"));
}

// Patching domain label!
try {
    $mainDomain = (new System_Model_Config())->find('main_domain', 'code');
    if ($mainDomain->getId()) {
        $mainDomain
            ->setLabel('Main siberian domain')
            ->save();
    }
} catch (\Exception $e) {
    // Silent!
}



// Editor
$logo = __get("logo");
$favicon = __get("favicon");

// Backoffice
$logoBackoffice = __get("logo_backoffice");
$faviconBackoffice = __get("favicon_backoffice");

// For migration purpose, empty backoffice logo must be set to the editor one!
if (empty($logoBackoffice)) {
    __set("logo_backoffice", $logo);
}

// For migration purpose, empty backoffice favicon must be set to the editor one!
if (empty($faviconBackoffice)) {
    __set("favicon_backoffice", $favicon);
}

// Enforcing https for external modules!
__set("use_https", "1");