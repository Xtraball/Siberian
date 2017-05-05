<?php

/**
 * Class Application_Model_Device_Ionic_Android_Abstract
 *
 * Generic class for Android Ionic based applications.
 */
abstract class Application_Model_Device_Ionic_Android_Abstract extends Application_Model_Device_Abstract {

    protected $_default_bundle_name = "com.appsmobilecompany.base";
    protected $_default_bundle_path = "com/appsmobilecompany/base";

    abstract public function prepareResources();

    /**
     * @param $application Application_Model_Application|Previewer_Model_Previewer
     * @throws Exception
     */
    protected function ionicResources($application) {

        /** Checking paths */
        $resource_folders = array(
            $this->_dest_source_res.'/drawable',
            $this->_dest_source_res.'/drawable-ldpi',
            $this->_dest_source_res.'/drawable-mdpi',
            $this->_dest_source_res.'/drawable-hdpi',
            $this->_dest_source_res.'/drawable-xhdpi',
            $this->_dest_source_res.'/drawable-xxhdpi',
            $this->_dest_source_res.'/drawable-xxxhdpi',
            $this->_dest_source_res.'/mipmap-hdpi',
            $this->_dest_source_res.'/mipmap-ldpi',
            $this->_dest_source_res.'/mipmap-mdpi',
            $this->_dest_source_res.'/mipmap-xhdpi',
            $this->_dest_source_res.'/mipmap-xxhdpi',
            $this->_dest_source_res.'/mipmap-xxxhdpi',
        );

        foreach($resource_folders as $folder) {
            if(!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
        }

        $push_icon = Core_Model_Directory::getBasePathTo("/images/application".$application->getAndroidPushIcon());

        /** icon/push_icon */
        $icons = array(
            $this->_dest_source_res.'/drawable-ldpi/icon.png'               => $application->getIcon(36, null, true),
            $this->_dest_source_res.'/drawable-mdpi/icon.png'               => $application->getIcon(48, null, true),
            $this->_dest_source_res.'/drawable-hdpi/icon.png'               => $application->getIcon(72, null, true),
            $this->_dest_source_res.'/drawable-xhdpi/icon.png'              => $application->getIcon(96, null, true),
            $this->_dest_source_res.'/drawable-xxhdpi/icon.png'             => $application->getIcon(144, null, true),
            $this->_dest_source_res.'/drawable-xxxhdpi/icon.png'            => $application->getIcon(192, null, true),
            $this->_dest_source_res.'/drawable-ldpi/push_icon.png'          => $application->getIcon(36, null, true),
            $this->_dest_source_res.'/drawable-mdpi/push_icon.png'          => $application->getIcon(48, null, true),
            $this->_dest_source_res.'/drawable-hdpi/push_icon.png'          => $application->getIcon(72, null, true),
            $this->_dest_source_res.'/drawable-xhdpi/push_icon.png'         => $application->getIcon(96, null, true),
            $this->_dest_source_res.'/drawable-xxhdpi/push_icon.png'        => $application->getIcon(144, null, true),
            $this->_dest_source_res.'/drawable-xxxhdpi/push_icon.png'       => $application->getIcon(192, null, true),
            $this->_dest_source_res.'/drawable/launcher_icon.png'           => $application->getIcon(48, null, true),
            $this->_dest_source_res.'/drawable-ldpi/launcher_icon.png'      => $application->getIcon(36, null, true),
            $this->_dest_source_res.'/drawable-mdpi/launcher_icon.png'      => $application->getIcon(48, null, true),
            $this->_dest_source_res.'/drawable-hdpi/launcher_icon.png'      => $application->getIcon(72, null, true),
            $this->_dest_source_res.'/drawable-xhdpi/launcher_icon.png'     => $application->getIcon(96, null, true),
            $this->_dest_source_res.'/drawable-xxhdpi/launcher_icon.png'    => $application->getIcon(144, null, true),
            $this->_dest_source_res.'/drawable-xxxhdpi/launcher_icon.png'   => $application->getIcon(192, null, true),
            $this->_dest_source.'/assets/www/img/app_icon.png'              => $application->getIcon(192, null, true),
            $this->_dest_source_res.'/drawable/ic_icon.png'                 => $push_icon,
            $this->_dest_source_res.'/drawable-hdpi/ic_icon.png'            => $push_icon,
            $this->_dest_source_res.'/drawable-ldpi/ic_icon.png'            => $push_icon,
            $this->_dest_source_res.'/drawable-mdpi/ic_icon.png'            => $push_icon,
            $this->_dest_source_res.'/drawable-xhdpi/ic_icon.png'           => $push_icon,
            $this->_dest_source_res.'/drawable-xxhdpi/ic_icon.png'          => $push_icon,
            $this->_dest_source_res.'/drawable-xxxhdpi/ic_icon.png'         => $push_icon,
            $this->_dest_source_res.'/mipmap-ldpi/icon.png'                 => $application->getIcon(36, null, true),
            $this->_dest_source_res.'/mipmap-mdpi/icon.png'                 => $application->getIcon(48, null, true),
            $this->_dest_source_res.'/mipmap-hdpi/icon.png'                 => $application->getIcon(72, null, true),
            $this->_dest_source_res.'/mipmap-xhdpi/icon.png'                => $application->getIcon(96, null, true),
            $this->_dest_source_res.'/mipmap-xxhdpi/icon.png'               => $application->getIcon(144, null, true),
            $this->_dest_source_res.'/mipmap-xxxhdpi/icon.png'              => $application->getIcon(192, null, true),
            $this->_dest_source_res.'/mipmap-ldpi/launcher_icon.png'        => $application->getIcon(36, null, true),
            $this->_dest_source_res.'/mipmap-mdpi/launcher_icon.png'        => $application->getIcon(48, null, true),
            $this->_dest_source_res.'/mipmap-hdpi/launcher_icon.png'        => $application->getIcon(72, null, true),
            $this->_dest_source_res.'/mipmap-xhdpi/launcher_icon.png'       => $application->getIcon(96, null, true),
            $this->_dest_source_res.'/mipmap-xxhdpi/launcher_icon.png'      => $application->getIcon(144, null, true),
            $this->_dest_source_res.'/mipmap-xxxhdpi/launcher_icon.png'     => $application->getIcon(192, null, true),
        );

        /** Associating screen image resolution to various landscape/portrait-resolution */
        $orientation_resolution_image = array(
            'land' => array(
                'ldpi' => 'standard',
                'mdpi' => 'standard',
                'hdpi' => 'standard',
                'xhdpi' => 'iphone_6',
                'xxhdpi' => 'iphone_6_plus',
                'xxxhdpi' => 'ipad_retina',
            ),
            'port' => array(
                'ldpi' => 'standard',
                'mdpi' => 'standard',
                'hdpi' => 'standard',
                'xhdpi' => 'iphone_6',
                'xxhdpi' => 'iphone_6_plus',
                'xxxhdpi' => 'ipad_retina',
            )
        );

        /** Clean up screen.xxx */
        array_map('unlink', glob("{$this->_dest_source_res}/drawable*/screen*"));

        foreach($orientation_resolution_image as $orientation => $resolution_image) {
            foreach($resolution_image as $resolution => $image) {
                $_file = $application->getStartupImageUrl($image, "base");
                $extension = pathinfo($_file, PATHINFO_EXTENSION);
                $icons[$this->_dest_source_res."/drawable-{$orientation}-{$resolution}/screen.$extension"] = $_file;
                $icons[$this->_dest_source_res."/drawable-{$orientation}-{$resolution}/startup_image.$extension"] = $_file;
            }
        }

        foreach($icons as $icon_dst => $icon_src) {
            if(Core_Model_Lib_Image::getMimeType($icon_src) != 'image/png') {
                list($width, $height) = getimagesize($icon_src);
                $newStartupImage = imagecreatetruecolor($width, $height);
                $startupSrc = imagecreatefromstring(file_get_contents($icon_src));
                imagecopyresized($newStartupImage, $startupSrc, 0, 0, 0, 0, $width, $height, $width, $height);

                $extension = pathinfo($icon_dst, PATHINFO_EXTENSION);
                $icon_dst = str_replace($extension, "png", $icon_dst);

                imagepng($newStartupImage, $icon_dst);
            } else {
                if(is_readable($icon_src) && is_writable(dirname($icon_dst))) {
                    if(!copy($icon_src, $icon_dst)) {
                        throw new Exception(__('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
                    }
                }
            }

            Siberian_Media::optimize($icon_dst);
        }

    }

    /** @TODO rename ${applicationId} >> XXX */
    protected function androidManifest() {
        /** Checking if the _application_id is a valid AndroidManifest id. */
        $tmp_application_id = $this->_application_id;
        if(!preg_match("#^[a-z]+#", $this->_application_id)) {
            $tmp_application_id = $this->_package_name.$tmp_application_id;
        }

        $replacements = array(
            $this->_default_bundle_name => $this->_package_name,
            '${applicationId}'          => $tmp_application_id,
        );

        /** App Only */
        if(isset($this->_previewer)) {
            $version_name = $this->_previewer->getAndroidVersion();
            $version_code = str_pad(str_replace('.', '', $version_name), 6, "0");

            if(($version_code != 1 && $version_code != 10000) || $version_name != "1.0") {
                $replacements = array_merge($replacements, array(
                    "versionCode=\"10000\"" => "versionCode=\"{$version_code}\"",
                    "versionName=\"1.0\"" => "versionName=\"{$version_name}\"",
                ));
            }
        } elseif($this->getDevice()) {
            $version_name = $this->getDevice()->getVersion();
            $version_code = str_pad(str_replace('.', '', $version_name), 6, "0");

            if(($version_code != 1 && $version_code != 10000) || $version_name != "1.0") {
                $replacements = array_merge($replacements, array(
                    "versionCode=\"10000\"" => "versionCode=\"{$version_code}\"",
                    "versionName=\"1.0\"" => "versionName=\"{$version_name}\"",
                ));
            }
        }

        $this->__replace($replacements, "{$this->_dest_source}/AndroidManifest.xml");
    }

    /**
     * Renaming main package classes
     *
     * @throws Exception
     */
    protected function renameMainPackage() {
        $replacements = array(
            $this->_default_bundle_name => $this->_package_name,
        );

        $links = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->_dest_source_src, 4096), RecursiveIteratorIterator::SELF_FIRST);
        foreach($links as $link) {
            if($link->isDir()) continue;
            $this->__replace($replacements, $link->getRealPath());
        }
    }

    /**
     * Editing gradle configurations
     *
     * @throws Exception
     */
    protected function gradleConfig() {

        $searchAndroid = "android {";
        $replaceAndroid = "android {
    lintOptions {
        checkReleaseBuilds false
        abortOnError false
    }
 	";

        $replacements = array(
            "com.android.tools.build:gradle:1.0.0+" => "com.android.tools.build:gradle:1.2.0+",
            $searchAndroid => $replaceAndroid,
        );

        $replacementsBase = $replacements;

        $this->__replace($replacementsBase, "{$this->_dest_source}/build.gradle");
        $this->__replace($replacements, "{$this->_dest_source}/CordovaLib/build.gradle");
    }

    /**
     * Duplicate missing languages, replace Custom values
     *
     * @param array $replacements
     * @throws Exception
     */
    protected function setStrings($replacements = array()) {
        $application_name = str_replace("'", "\\'", $this->_application_name);

        $replacements = array_merge($replacements,
            array("#<string name=\"app_name\">(.*)</string>#mi" => "<string name=\"app_name\"><![CDATA[{$application_name}]]></string>"));

        $replacements_config = array(
            "#<name>AppsMobileCompany</name>#mi"    => "<name><![CDATA[{$application_name}]]></name>",
            "#<description>(.*)</description>#mi"   => "<description><![CDATA[{$application_name} app source code]]></description>",
            "#<author>(.*)</author>#mi"             => "<author><![CDATA[{$application_name} Dev Team]]></author>",
        );

        $this->__replace($replacements, "{$this->_dest_source_res}/values/strings.xml", true);

        // Retrieve the available languages
        $languages = Core_Model_Language::getLanguageCodes();

        // Check if all the available languages exist in the Android source
        foreach($languages as $lang) {

            if(stripos($lang, "_") !== false) {
                $lang = explode("_", $lang);
                if(count($lang) == 2) {
                    $lang[1] = "r".$lang[1];
                }
                $lang = implode("-", $lang);
            }

            /** Specific case */
            if($lang == "es-r419") {
                $lang = "es-rUS";
            }
            if($lang == "zh-rHant") {
                $lang = "zh-rTW";
            }
            if($lang == "zh-rHans") {
                $lang = "zh-rCN";
            }

            // If not, create them out of the English one.
            if (!file_exists("{$this->_dest_source_res}/values-{$lang}/strings.xml")) {
                mkdir("{$this->_dest_source_res}/values-{$lang}", 0777);
                copy("{$this->_dest_source_res}/values/strings.xml", "{$this->_dest_source_res}/values-{$lang}/strings.xml");
            }

            $this->__replace($replacements, "{$this->_dest_source_res}/values-{$lang}/strings.xml", true);
        }


        $this->__replace($replacements_config, $this->_dest_source_res.'/xml/config.xml', true);
        $this->__replace(array($this->_default_bundle_name => $this->_package_name), $this->_dest_source_res.'/xml/config.xml');
    }

    protected function _admob() {
        $app_js_path = $this->_dest_source."/assets/www/js/app.js";

        $this->__replace(array("#,\s*isTesting:\s+true#ims" => ""), $app_js_path, true);
    }

}
