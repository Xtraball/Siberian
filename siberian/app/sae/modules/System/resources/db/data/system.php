<?php
$module = new Installer_Model_Installer_Module();
$module->prepare("System", false);

/** Force flat. */
$editor_design = "flat";

/** Disable cron for Fresh sae install */
$disable_cron = (!$module->isInstalled() && Siberian_Version::is("SAE")) ? "1" : "0";
$environment = "production";

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
        "code" => "app_default_identifier_android",
        "label" => "Application base Package Name (Android)",
        "value" => ""
    ),
    array(
        "code" => "app_default_identifier_ios",
        "label" => "Application base Bundle ID (iOS)",
        "value" => ""
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
        "code" => "application_ios_owner_admob_interstitial_id",
        "label" => "Admob Interstitial ID for platform owner (Ios)"
    ),
    array(
        "code" => "application_ios_owner_admob_type",
        "label" => "Admob type for platform owner (Ios)"
    ),
    array(
        "code" => "application_ios_owner_admob_weight",
        "label" => "Admob split revenue for platform owner (Ios)",
        "value" => 100
    ),
    array(
        "code" => "application_android_owner_admob_id",
        "label" => "Admob ID for platform owner (Android)"
    ),
    array(
        "code" => "application_android_owner_admob_interstitial_id",
        "label" => "Admob Interstitial ID for platform owner (Android)"
    ),
    array(
        "code" => "application_android_owner_admob_type",
        "label" => "Admob type for platform owner (Android)"
    ),
    array(
        "code" => "application_android_owner_admob_weight",
        "label" => "Admob split revenue for platform owner (Android)",
        "value" => 100
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
        "code" => "siberiancms_key",
        "label" => "Your CMS License Key",
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
        "value" => "0"
    ),
    array(
        "code" => "global_quota",
        "label" => "Disk usage quota in Mb",
        "value" => "10000"
    ),
    array(
        "code" => "enable_custom_smtp",
        "label" => "Enable custom SMTP configuration",
        "value" => "0"
    ),
    array(
        "code" => "vat_check_activated",
        "label" => "Vat check activated",
        "value" => "0"
    ),
    array(
        "code" => "invoice_footer_message",
        "label" => "Invoices footer message",
        "value" => ""
    ),
    array(
        "code" => "privacy_policy",
        "label" => "Default privacy policy",
        "value" => "
<h1><strong>Privacy Policy of the #APP_NAME application</strong></h1>
This Application collects some Personal Data from its Users.
<h2><strong>Data Controller and Owner</strong></h2>
&nbsp;
<h2><strong>Types of Data collected</strong></h2>
<p>Among the types of Personal Data that this Application collects, by itself or through third parties, there are: Geographic position, Cookie and Usage Data. Other Personal Data collected may be described in other sections of this privacy policy or by dedicated explanation text contextually with the Data collection. The Personal Data may be freely provided by the User, or collected automatically when using this Application. Any use of Cookies - or of other tracking tools - by this Application or by the owners of third party services used by this Application, unless stated otherwise, serves to identify Users and remember their preferences, for the sole purpose of providing the service required by the User. Failure to provide certain Personal Data may make it impossible for this Application to provide its services. The User assumes responsibility for the Personal Data of third parties published or shared through this Application and declares to have the right to communicate or broadcast them, thus relieving the Data Controller of all responsibility.</p>
<h2><strong>Mode and place of processing the Data</strong></h2>
<h3><strong>Methods of processing</strong></h3>
<p>The Data Controller processes the Data of Users in a proper manner and shall take appropriate security measures to prevent unauthorized access, disclosure, modification, or unauthorized destruction of the Data. The Data processing is carried out using computers and/or IT enabled tools, following organizational procedures and modes strictly related to the purposes indicated. In addition to the Data Controller, in some cases, the Data may be accessible to certain types of persons in charge, involved with the operation of the site (administration, sales, marketing, legal, system administration) or external parties (such as third party technical service providers, mail carriers, hosting providers, IT companies, communications agencies) appointed, if necessary, as Data Processors by the Owner. The updated list of these parties may be requested from the Data Controller at any time.</p>
<h3><strong>Place</strong></h3>
<p>The Data is processed at the Data Controller&#39;s operating offices and in any other places where the parties involved with the processing are located. For further information, please contact the Data Controller.</p>
<h3><strong>Retention time</strong></h3>
<p>The Data is kept for the time necessary to provide the service requested by the User, or stated by the purposes outlined in this document, and the User can always request that the Data Controller suspend or remove the data.</p>
<h2><strong>The use of the collected Data</strong></h2>
<p>The Data concerning the User is collected to allow the Application to provide its services, as well as for the following purposes: Access to third party services&#39; accounts, Location-based interactions, Content commenting and Interaction with external social networks and platforms. The Personal Data used for each purpose is outlined in the specific sections of this document.</p>
<h2><strong>Facebook permissions asked by this Application</strong></h2>
<p>This Application may ask some Facebook permissions allowing it to perform actions with the User&#39;s Facebook account and to retrieve information, including Personal Data, from it. For more information about the following permissions, refer to the Facebook permissions documentation and to the Facebook privacy policy. The permissions asked are the following:</p>
<h3><strong>Basic information</strong></h3>
<p>By default, this includes certain User&rsquo;s Data such as id, name, picture, gender, and their locale. Certain connections of the User, such as the Friends, are also available. If the user has made more of their data public, more information will be available.</p>
<h3><strong>Checkins</strong></h3>
<p>Provides read access to the authorized user&#39;s check-ins</p>
<h3><strong>Email</strong></h3>
<p>Provides access to the user&#39;s primary email address</p>
<h3><strong>Likes</strong></h3>
<p>Provides access to the list of all of the pages the user has liked.</p>
<h3><strong>Photos</strong></h3>
<p>Provides access to the photos the user has uploaded, and photos the user has been tagged in.</p>
<h3><strong>Publish App Activity</strong></h3>
<p>Allows the app to publish to the Open Graph using Built-in Actions, Achievements, Scores, or Custom Actions. The app can also publish other activity which is detailed in the Facebook&#39;s Publishing Permissions document.</p>
<h2><strong>Detailed information on the processing of Personal Data</strong></h2>
<p>Personal Data is collected for the following purposes and using the following services:</p>
<h3><strong>Access to third party services&#39; accounts</strong></h3>
<p>These services allow this Application to access Data from your account on a third party service and perform actions with it. These services are not activated automatically, but require explicit authorization by the User.<br />
<strong>Access to the Facebook account (This Application)</strong><br />
This service allows this Application to connect with the User&#39;s account on the Facebook social network, provided by Facebook Inc. Permissions asked: Checkins, Email, Likes, Photos and Publish App Activity. Place of processing : USA &ndash; Privacy Policy</p>
<h3><strong>Content commenting</strong></h3>
<p>Content commenting services allow Users to make and publish their comments on the contents of this Application. Depending on the settings chosen by the Owner, Users may also leave anonymous comments. If there is an email address among the Personal Data provided by the User, it may be used to send notifications of comments on the same content. Users are responsible for the content of their own comments. If a content commenting service provided by third parties is installed, it may still collect web traffic data for the pages where the comment service is installed, even when users do not use the content commenting service.<br />
<strong>Facebook Comments (Facebook)</strong><br />
Facebook Comments is a content commenting service provided by Facebook Inc. enabling the User to leave comments and share them on the Facebook platform. Personal Data collected: Cookie and Usage Data. Place of processing : USA &ndash; Privacy Policy</p>
<h3><strong>Interaction with external social networks and platforms</strong></h3>
<p>These services allow interaction with social networks or other external platforms directly from the pages of this Application. The interaction and information obtained by this Application are always subject to the User&rsquo;s privacy settings for each social network. If a service enabling interaction with social networks is installed it may still collect traffic data for the pages where the service is installed, even when Users do not use it.<br />
<strong>Facebook Like button and social widgets (Facebook)</strong><br />
The Facebook Like button and social widgets are services allowing interaction with the Facebook social network provided by Facebook Inc. Personal Data collected: Cookie and Usage Data. Place of processing : USA &ndash; Privacy Policy</p>
<h3><strong>Location-based interactions</strong></h3>
<p><strong>Geolocation (This Application)&nbsp;</strong><br />
This Application may collect, use, and share User location Data in order to provide location-based services. Most browsers and devices provide tools to opt out from this feature by default. If explicit authorization has been provided, the User&rsquo;s location data may be tracked by this Application. Personal Data collected: Geographic position.</p>
<h2><strong>Additional information about Data collection and processing</strong></h2>
<h3><strong>Legal Action</strong></h3>
<p>The User&#39;s Personal Data may be used for legal purposes by the Data Controller, in Court or in the stages leading to possible legal action arising from improper use of this Application or the related services.<br />
The User is aware of the fact that the Data Controller may be required to reveal personal data upon request of public authorities.</p>
<h3><strong>Additional information about User&#39;s Personal Data</strong></h3>
<p>In addition to the information contained in this privacy policy, this Application may provide the User with additional and contextual information concerning particular services or the collection and processing of Personal Data upon request.</p>
<h3><strong>System Logs and Maintenance</strong></h3>
<p>For operation and maintenance purposes, this Application and any third party services may collect files that record interaction with this Application (System Logs) or use for this purpose other Personal Data (such as IP Address).</p>
<h3><strong>Information not contained in this policy</strong></h3>
<p>More details concerning the collection or processing of Personal Data may be requested from the Data Controller at any time. Please see the contact information at the beginning of this document.</p>
<h3><strong>The rights of Users</strong></h3>
<p>Users have the right, at any time, to know whether their Personal Data has been stored and can consult the Data Controller to learn about their contents and origin, to verify their accuracy or to ask for them to be supplemented, cancelled, updated or corrected, or for their transformation into anonymous format or to block any data held in violation of the law, as well as to oppose their treatment for any and all legitimate reasons. Requests should be sent to the Data Controller at the contact information set out above.<br />
This Application does not support &ldquo;Do Not Track&rdquo; requests.<br />
To determine whether any of the third party services it uses honor the &ldquo;Do Not Track&rdquo; requests, please read their privacy policies.</p>
<h3><strong>Changes to this privacy policy</strong></h3>
<p>The Data Controller reserves the right to make changes to this privacy policy at any time by giving notice to its Users on this page. It is strongly recommended to check this page often, referring to the date of the last modification listed at the bottom. If a User objects to any of the changes to the Policy, the User must cease using this Application and can request that the Data Controller erase the Personal Data. Unless stated otherwise, the then-current privacy policy applies to all Personal Data the Data Controller has about Users.</p>
<h2><strong>Definitions and legal references</strong></h2>
<h3><strong>Personal Data (or Data)</strong></h3>
<p>Any information regarding a natural person, a legal person, an institution or an association, which is, or can be, identified, even indirectly, by reference to any other information, including a personal identification number.</p>
<h3><strong>Usage Data</strong></h3>
<p>Information collected automatically from this Application (or third party services employed in this Application ), which can include: the IP addresses or domain names of the computers utilized by the Users who use this Application, the URI addresses (Uniform Resource Identifier), the time of the request, the method utilized to submit the request to the server, the size of the file received in response, the numerical code indicating the status of the server&#39;s answer (successful outcome, error, etc.), the country of origin, the features of the browser and the operating system utilized by the User, the various time details per visit (e.g., the time spent on each page within the Application) and the details about the path followed within the Application with special reference to the sequence of pages visited, and other parameters about the device operating system and/or the User&#39;s IT environment.</p>
<h3><strong>User</strong></h3>
<p>The individual using this Application, which must coincide with or be authorized by the Data Subject, to whom the Personal Data refer.</p>
<h3><strong>Data Subject</strong></h3>
<p>The legal or natural person to whom the Personal Data refers to.</p>
<h3><strong>Data Processor (or Data Supervisor)</strong></h3>
<p>The natural person, legal person, public administration or any other body, association or organization authorized by the Data Controller to process the Personal Data in compliance with this privacy policy.</p>
<h3><strong>Data Controller (or Owner)</strong></h3>
<p>The natural person, legal person, public administration or any other body, association or organization with the right, also jointly with another Data Controller, to make decisions regarding the purposes, and the methods of processing of Personal Data and the means used, including the security measures concerning the operation and use of this Application. The Data Controller, unless otherwise specified, is the Owner of this Application.</p>
<h3><strong>This Application</strong></h3>
<p>The hardware or software tool by which the Personal Data of the User is collected.</p>
<h3><strong>Cookie</strong></h3>
<p>Small piece of data stored in the User&#39;s device.</p>
<h3><strong>Legal information</strong></h3>
<p>Notice to European Users: this privacy statement has been prepared in fulfillment of the obligations under Art. 10 of EC Directive n. 95/46/EC, and under the provisions of Directive 2002/58/EC, as revised by Directive 2009/136/EC, on the subject of Cookies. This privacy policy relates solely to this Application.</p>
<p><em><strong>Latest update: June 11, 2014.</strong></em></p>
",
    ),
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

# 4.8.7: Maintenance, remove blank entries
$this->query("DELETE FROM `system_config` WHERE code = '';");

# 4.10.0: Clear locks
Siberian_Cache::__clearLocks();

# 4.12.0
$id_android = System_Model_Config::getValueFor("app_default_identifier_android");
$id_ios = System_Model_Config::getValueFor("app_default_identifier_ios");

$buildId = function($suffix) {
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $url = mb_strtolower($request->getServer("HTTP_HOST"));
    $url = array_reverse(explode(".", $url));
    $url[] = $suffix;

    foreach($url as &$part) {
        $part = preg_replace("/[^0-9a-z\.]/i", "", $part);
    }

    return implode(".", $url);
};

if(empty($id_android)) {
    System_Model_Config::setValueFor("app_default_identifier_android", $buildId("android"));
}

if(empty($id_ios)) {
    System_Model_Config::setValueFor("app_default_identifier_ios", $buildId("ios"));
}

