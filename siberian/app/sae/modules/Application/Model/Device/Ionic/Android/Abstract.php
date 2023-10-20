<?php

use Siberian\Exception;

/**
 * Class Application_Model_Device_Ionic_Android_Abstract
 *
 * Generic class for Android Ionic based applications.
 *
 * @method integer getId()
 */
abstract class Application_Model_Device_Ionic_Android_Abstract extends Application_Model_Device_Abstract
{
    /**
     * @var string
     */
    protected $_default_bundle_name = 'com.appsmobilecompany.base';

    /**
     * @var string
     */
    protected $_default_bundle_path = 'com/appsmobilecompany/base';

    /**
     * @return mixed
     */
    abstract public function prepareResources();

    /**
     * @param $application Application_Model_Application|Previewer_Model_Previewer
     * @throws Exception
     */
    protected function ionicResources($application)
    {
        /** Checking paths */
        $resourceFolders = [
            $this->_dest_source_res . '/drawable',
            $this->_dest_source_res . '/drawable-xxxhdpi',
            $this->_dest_source_res . '/mipmap-xxxhdpi',
        ];

        foreach ($resourceFolders as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
        }

        $pushIcon = path('/images/application' . $application->getAndroidPushIcon());

        /** icon/push_icon */
        $appIcon192 = $application->getIcon(192, null, true);
        $appIcon96 = $application->getIcon(96, null, true);
        $icons = [
            $this->_dest_source_res . '/drawable-xxxhdpi/icon.png' => $appIcon192,
            $this->_dest_source_res . '/drawable-xxxhdpi/push_icon.png' => $appIcon192,
            $this->_dest_source_res . '/drawable-xxxhdpi/ic_launcher.png' => $appIcon192,
            $this->_dest_source_res . '/drawable-xxxhdpi/launcher_icon.png' => $appIcon192,

            $this->_dest_source_res . '/mipmap-xxxhdpi/ic_launcher.png' => $appIcon192,
            $this->_dest_source_res . '/mipmap-xxxhdpi/icon.png' => $appIcon192,
            $this->_dest_source_res . '/mipmap-xxxhdpi/launcher_icon.png' => $appIcon192,

            $this->_dest_source_res . '/drawable/icon.png' => $appIcon96,
            $this->_dest_source_res . '/drawable/ic_icon.png' => $pushIcon,
            $this->_dest_source_res . '/drawable/ic_stat_onesignal_default.png' => $pushIcon,
            $this->_dest_source_res . '/drawable-hdpi/ic_icon.png' => $pushIcon,
            $this->_dest_source_res . '/drawable-hdpi/ic_stat_onesignal_default.png' => $pushIcon,
            $this->_dest_source_res . '/drawable-xxxhdpi/ic_icon.png' => $pushIcon,
            $this->_dest_source_res . '/drawable-xxxhdpi/ic_stat_onesignal_default.png' => $pushIcon,

            $this->_dest_source . '/app/src/main/assets/www/img/app_icon.png' => $appIcon192,
        ];

        /** Clean up screen.xxx, port-*, land-* */
        array_map('unlink', glob("{$this->_dest_source_res}/drawable*/screen*"));
        array_map('rmdir', glob("{$this->_dest_source_res}/drawable-land-*"));
        array_map('rmdir', glob("{$this->_dest_source_res}/drawable-port-*"));

        $_file = $application->getStartupBackgroundUnified();

        // Convert to jpeg
        $jpegStartup = Siberian_Image::open(path($_file));
        $_tmpStartup = path('/var/tmp/' . uniqid('startup_', true) . '.jpg');
        $jpegStartup->save($_tmpStartup, 'jpg', 70);

        foreach ($icons as $icon_dst => $icon_src) {
            if (Core_Model_Lib_Image::getMimeType($icon_src) != 'image/png') {
                list($width, $height) = getimagesize($icon_src);
                $newStartupImage = imagecreatetruecolor($width, $height);
                $startupSrc = imagecreatefromstring(file_get_contents($icon_src));
                imagecopyresized($newStartupImage, $startupSrc, 0, 0, 0, 0, $width, $height, $width, $height);

                $extension = pathinfo($icon_dst, PATHINFO_EXTENSION);
                $icon_dst = str_replace($extension, "png", $icon_dst);

                imagepng($newStartupImage, $icon_dst);
            } else {
                if (is_readable($icon_src) && is_writable(dirname($icon_dst))) {
                    if (!copy($icon_src, $icon_dst)) {
                        throw new Exception(__('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
                    }
                }
            }

            Siberian_Media::optimize($icon_dst);
        }

        // Startup screen
        $startupDest = $this->_dest_source_res . '/drawable-xxxhdpi/screen.jpg';
        if (is_readable($_tmpStartup) && is_writable(dirname($startupDest))) {
            if (!copy($_tmpStartup, $startupDest)) {
                throw new Exception(__('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
            }
        }

        Siberian_Media::optimize($startupDest);
    }



    /**
     * @throws Exception
     */
    protected function androidManifest()
    {
        /** Checking if the _application_id is a valid AndroidManifest id. */
        $device = $this->getDevice();
        $tmp_application_id = $this->_application_id;
        if (!preg_match("#^[a-z]+#", $this->_application_id)) {
            $tmp_application_id = $this->_package_name . $tmp_application_id;
        }

        /**
         * @var Application_Model_Application $application
         */
        $application = $this->getApplication();

        $orientations = Siberian_Json::decode($device->getOrientations());
        $android = $orientations['android'];

        $androidValids = [
            'landscape',
            'portrait',
            'reverseLandscape',
            'reversePortrait',
            'sensorPortrait',
            'sensorLandscape',
            'fullSensor',
        ];

        if (!in_array($android, $androidValids, false)) {
            $android = 'fullSensor';
        }

        $replacements = [
            $this->_default_bundle_name => $this->_package_name,
            '${applicationId}' => $tmp_application_id,
            "android:screenOrientation=\"unspecified\"" => "android:screenOrientation=\"{$android}\"",
        ];

        $versionName = $device->getVersion();
        $device->setBuildNumber($device->getBuildNumber() + 1)->save();
        $versionCode = Application_Model_Device_Abstract::validatedVersion($device);

        $replacements = array_merge($replacements, [
            "versionCode=\"10000\"" => "versionCode=\"{$versionCode}\"",
            "versionName=\"1.0\"" => "versionName=\"{$versionName}\"",
        ]);

        // Battery optimization
        $disableBatteryOptimization = (boolean) filter_var($application->getDisableBatteryOptimization(), FILTER_VALIDATE_BOOLEAN);
        if (!$disableBatteryOptimization) {
            $replacements = array_merge($replacements, [
                "<uses-permission android:name=\"android.permission.REQUEST_IGNORE_BATTERY_OPTIMIZATIONS\" />" => ""
            ]);
        }

        // Disable *_location
        $disableLocation = (boolean) filter_var($application->getDisableLocation(), FILTER_VALIDATE_BOOLEAN);
        //<uses-permission.*ACCESS_.*_LOCATION" \/>
        if ($disableLocation) {
            $replacements = array_merge($replacements, [
                "<uses-permission android:name=\"android.permission.ACCESS_COARSE_LOCATION\" />" => "",
                "<uses-permission android:name=\"android.permission.ACCESS_FINE_LOCATION\" />" => "",
                "<uses-feature android:name=\"android.hardware.location.gps\" android:required=\"false\" />" => ""
            ]);
        }

        $radio = (new Application_Model_Option())->find('radio', 'code');

        $features = $application->getOptions();
        $hasRadio = false;
        foreach ($features as $feature) {
            if ($feature->getOptionId() === $radio->getId()) {
                $hasRadio = true;
                break;
            }
        }

        // Ensure the clearText is not already applied by someone else before!
        $androidManifestContent = file_get_contents("{$this->_dest_source}/app/src/main/AndroidManifest.xml");

        if (stripos($androidManifestContent, 'android:usesCleartextTraffic') === false &&
            $hasRadio) {
            // Ok we can add it safely!
            $replacements = array_merge($replacements, [
                "<application " => "<application android:usesCleartextTraffic=\"true\" ",
                'ca-app-pub-0000000000000000~0000000000' => $this->admobAppIdentifier
            ]);
        }

        $this->__replace($replacements, "{$this->_dest_source}/app/src/main/AndroidManifest.xml");

        // config.xml
        $this->__replace([
            '${applicationId}' => $tmp_application_id,
            'ca-app-pub-0000000000000000~0000000000' => $this->admobAppIdentifier
        ], "{$this->_dest_source}/app/src/main/res/xml/config.xml");

        // FB Mediation
        if ($application->getMediationFacebook() !== '1') {
            $this->__replace([
                '#implementation \"com\.facebook\.android\:audience\-network\-sdk\:\+\"#' => '',
                '#implementation \"com\.google\.ads\.mediation\:facebook\:\+\"#' => ''
            ], "{$this->_dest_source}/app/build.gradle", true);
            $this->__replace([
                '#cordova\.system\.library\.([0-9]+)\=com\.facebook\.android\:audience\-network\-sdk\:\+#' => '',
                '#cordova\.system\.library\.([0-9]+)\=com\.google\.ads\.mediation\:facebook\:\+#' => ''
            ], "{$this->_dest_source}/project.properties", true);
        }

        // StartApp Mediation
        if ($application->getMediationStartapp() !== '1') {
            $this->__replace([
                '#implementation \"com\.startapp\:inapp\-sdk\:([0-9\.\+]+)\"#' => '',
                '#implementation \"com\.startapp\:admob\-mediation\:([0-9\.\+]+)\"#' => ''
            ], "{$this->_dest_source}/app/build.gradle", true);
            $this->__replace([
                '#cordova\.system.library\.([0-9]+)\=com\.startapp\:inapp\-sdk\:([0-9\.\+]+)#' => '',
                '#cordova\.system.library\.([0-9]+)\=com\.startapp\:admob\-mediation\:([0-9\.\+]+)#' => ''
            ], "{$this->_dest_source}/project.properties", true);
        }
    }

    /**
     * Renaming main package classes
     *
     * @throws Exception
     */
    protected function renameMainPackage()
    {
        $replacements = [
            $this->_default_bundle_name => $this->_package_name,
        ];

        $links = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->_dest_source_src, 4096),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($links as $link) {
            if ($link->isDir()) {
                continue;
            }
            $this->__replace($replacements, $link->getRealPath());
        }
    }

    /**
     * Duplicate missing languages, replace Custom values
     *
     * @param array $replacements
     * @throws Exception
     */
    protected function setStrings($replacements = [])
    {
        $application_name = str_replace("'", "\\'", $this->_application_name);

        $replacements = array_merge($replacements,
            ["#<string name=\"app_name\">(.*)</string>#mi" => "<string name=\"app_name\"><![CDATA[{$application_name}]]></string>"]);

        $replacements_config = [
            "#<name>AppsMobileCompany</name>#mi" => "<name><![CDATA[{$application_name}]]></name>",
            "#<description>(.*)</description>#mi" => "<description><![CDATA[{$application_name} app source code]]></description>",
            "#<author>(.*)</author>#mi" => "<author><![CDATA[{$application_name} Dev Team]]></author>",
        ];

        $this->__replace($replacements, "{$this->_dest_source_res}/values/strings.xml", true);

        // Retrieve the available languages
        $languages = Core_Model_Language::getLanguageCodes();

        // Check if all the available languages exist in the Android source
        foreach ($languages as $lang) {

            if (stripos($lang, "_") !== false) {
                $lang = explode("_", $lang);
                if (count($lang) == 2) {
                    $lang[1] = "r" . $lang[1];
                }
                $lang = implode_polyfill("-", $lang);
            }

            /** Specific case */
            if ($lang === 'es-r419') {
                $lang = 'es-rUS';
            }
            if ($lang === 'zh-rHant') {
                $lang = 'zh-rTW';
            }
            if ($lang === 'zh-rHans') {
                $lang = 'zh-rCN';
            }

            // If not, create them out of the English one.
            if (!file_exists("{$this->_dest_source_res}/values-{$lang}/strings.xml")) {
                mkdir("{$this->_dest_source_res}/values-{$lang}", 0777);
                copy("{$this->_dest_source_res}/values/strings.xml", "{$this->_dest_source_res}/values-{$lang}/strings.xml");
            }

            $this->__replace($replacements, "{$this->_dest_source_res}/values-{$lang}/strings.xml", true);
        }


        $this->__replace($replacements_config, $this->_dest_source_res . '/xml/config.xml', true);
        $this->__replace([$this->_default_bundle_name => $this->_package_name], $this->_dest_source_res . '/xml/config.xml');
    }

}
