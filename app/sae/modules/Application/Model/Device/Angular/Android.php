<?php

class Application_Model_Device_Angular_Android extends Application_Model_Device_Abstract {

    const SOURCE_FOLDER = "/var/apps/angular/android/Siberian";
    const DEST_FOLDER = "/var/tmp/applications/angular/android/%s/Siberian";

    protected $_current_version = '1.0';
    protected $_folder_name = '';
    protected $_formatted_bundle_name = '';
    protected $_dst;
    protected $_sources_dst;
    protected $_base_dst;
    protected $_zipname;
    protected $_package_name;

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_os_name = "android";
        return $this;
    }

    public function getCurrentVersion() {
        return $this->_current_version;
    }

    public function getStoreName() {
        return "Google Play";
    }

    public function getBrandName() {
        return "Google";
    }

    public function getResources() {

        $umask = umask(0);

        $src = $this->prepareResources();

        umask($umask);

        return $src;

    }

    public function prepareResources() {

        $this->_package_name = $this->getApplication()->getBundleId();

        $this->_generatePasswords();
        $this->_cpFolder();
        $this->_prepareFiles();
        $this->_copyImages();

        if($this->getDevice()->getDownloadType() != "apk") {
            $zip = $this->_zipFolder();
            return $zip;
        }

        if($apk = $this->_generateApk()) {
            return $apk;
        }

    }

    protected function _generatePasswords() {

        $save = false;
        $device = $this->getDevice();
        $passwords = array(
            "store_pass" => $device->getStorePass(),
            "key_pass" => $device->getKeyPass(),
            "alias" => $device->getAlias()
        );

        if(empty($passwords["store_pass"])) {
            $store_pass = Core_Model_Lib_String::generate(8);
            $device->setStorePass($store_pass);
            $passwords["store_pass"] = $store_pass;
            $save = true;
        }
        if(empty($passwords["key_pass"])) {
            $key_pass = Core_Model_Lib_String::generate(8);
            $device->setKeyPass($key_pass);
            $passwords["key_pass"] = $key_pass;
            $save = true;
        }
        if(empty($passwords["alias"])) {
            $alias = Core_Model_Lib_String::format($this->getApplication()->getName(), true);
            if(!$alias) $alias = $this->getApplication()->getId();

            $device->setAlias($alias);
            $passwords["alias"] = $alias;

            $save = true;
        }

        if($save) {
            Zend_Registry::get("logger")->sendException(print_r($passwords, true), "android_pwd_{$device->getAppId()}_", false);
            $device->save();
        }

        return $this;

    }

    protected function _cpFolder() {

        $this->_folder_name = $this->getDevice()->getTmpFolderName();

        $src = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER);
        $dst = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER);
        $dst = sprintf($dst, $this->_folder_name);

        // Supprime le dossier s'il existe puis le créé
        if(is_dir($dst)) Core_Model_Directory::delete($dst);
        mkdir($dst, 0777, true);

        // Copie les sources
        Core_Model_Directory::duplicate($src, $dst);

        $this->_zipname = $this->getDevice()->getAlias().'_android_source';;

        $this->_dst = $dst;

        $this->_sources_dst = "$dst/app/src/main";


        $src = $this->_sources_dst.'/java/com/appsmobilecompany/base';
        $dst = $this->_sources_dst.'/java/'.str_replace(".", "/", $this->_package_name);

        Core_Model_Directory::move($src, $dst);
        Core_Model_Directory::delete($this->_sources_dst.'/java/com/appsmobilecompany');

        return $this;

    }

    protected function _prepareFiles() {

//        $source = $this->_sources_dst.'/java/com/'.$this->_formatted_bundle_name.'/'.$this->_folder_name;
        $source = $this->_sources_dst.'/java/'.str_replace(".", "/", $this->_package_name);
        $links = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, 4096), RecursiveIteratorIterator::SELF_FIRST);
        $allowed_extensions = array("java", "xml");

        if(!$links) return $this;

        foreach($links as $link) {

            if(!$link->isDir()) {

                $info = pathinfo($link->getPathName());
                $extension = $info["extension"];

                if(in_array($extension, $allowed_extensions)) {
                    if (strpos($link, 'CommonUtilities.java') !== false) {

                        if(defined("CRON")) {
                            $url = "http://".$this->getDevice()->getHost()."/";
                        } else {
                            $url = $this->getUrl();
                        }

                        $this->__replace(array(
                            'String SENDER_ID = ""' => 'String SENDER_ID = "' . Push_Model_Certificate::getAndroidSenderId() . '"',
                            'String APP_ID = ""' => 'String APP_ID = "' . $this->getApplication()->getId() . '"',
                            'SERVEUR_URL = "http://base.appsmobilecompany.com/";' => 'SERVEUR_URL = "' . $url . '";'
                        ), $link);
                    }
                }
            }
        }

        $replacements = array(
            'com.appsmobilecompany.base' => $this->_package_name,
            '[ENCRYPT_KEY]' => $this->getDevice()->getKeyPass()
        );
        $source = $this->_dst;
        $links = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, 4096), RecursiveIteratorIterator::SELF_FIRST);
        foreach($links as $link) {
            if($link->isDir()) continue;
            $this->__replace($replacements, $link->getRealPath());
        }
        $this->__replace($replacements, $this->_sources_dst.'/AndroidManifest.xml');
        $this->__replace($replacements, $this->_sources_dst.'/../../build.gradle');


        $name = str_replace("'", "\\'", $this->getApplication()->getName());
        // Retrieve the available languages
        $languages = Core_Model_Language::getLanguageCodes();
        // Check if all the available languages exist in the Android source
        foreach($languages as $lang) {
            if($lang == "en") continue;

            if(stripos($lang, "_") !== false) {
                $lang = explode("_", $lang);
                if(count($lang) == 2) {
                    $lang[1] = "r".$lang[1];
                }
                $lang = implode("-", $lang);
            }

            // If not, create them out of the English one.
            if (!file_exists($this->_sources_dst . '/res/values-' . $lang . '/strings.xml')) {
                mkdir($this->_sources_dst . '/res/values-' . $lang, 0777);
                copy($this->_sources_dst . '/res/values/strings.xml', $this->_sources_dst . '/res/values-' . $lang . '/strings.xml');
            }
        }

        if(defined("CRON")) {
            $url_app = "http://".$this->getDevice()->getHost()."/en/".$this->getApplication()->getKey();
        } else {
            $url_app = $this->getApplication()->getUrl(null, array(), 'en', false);
        }

        $replacements = array(
            'http://localhost/overview' => $url_app,
            '<string name="app_name">Apps Mobile Company</string>' => '<string name="app_name"><![CDATA['.$name.']]></string>',
        );

        $this->__replace($replacements, $this->_sources_dst.'/res/values/strings.xml');

        if($fbk_app_id = $this->getApplication()->getFacebookId()) {
            $replacements_sharing = array(
                '<!-- FACEBOOK -->' => '<meta-data android:name="com.facebook.sdk.ApplicationId" android:value="@string/facebook_app_id"/>'
            );

            if($this->getApplication()->getData("facebook_id")) {
                $replacements_sharing['<!-- FACEBOOK PROVIDER -->'] = '<provider android:authorities="com.facebook.app.FacebookContentProvider'.$fbk_app_id.'"
                                            android:name="com.facebook.FacebookContentProvider"
                                            android:exported="true" />';
            }

            $replacements_sharing_ids = array(
                '[FACEBOOK_APP_ID]' => $fbk_app_id
            );

            $this->__replace($replacements_sharing_ids, $this->_sources_dst.'/res/values/sharings_id.xml');
            $this->__replace($replacements_sharing, $this->_sources_dst.'/AndroidManifest.xml');
        }

        foreach($languages as $lang) {

            if($lang == "en") continue;

            if(stripos($lang, "_") !== false) {
                $android_lang = explode("_", $lang);
                if(count($android_lang) == 2) {
                    $android_lang[1] = "r".$android_lang[1];
                }
                $android_lang = implode("-", $android_lang);
            } else {
                $android_lang = $lang;
            }

            if(defined("CRON")) {
                $url_app = "http://".$this->getDevice()->getHost()."/".$lang."/".$this->getApplication()->getKey();
            } else {
                $url_app = $this->getApplication()->getUrl(null, array(), $lang, false);
            }

            $replacements = array(
                'http://localhost/overview' => $url_app,
                '<string name="app_name">SiberianCMS</string>' => '<string name="app_name"><![CDATA['.$name.']]></string>',
                '<string name="app_name">Apps Mobile Company</string>' => '<string name="app_name"><![CDATA['.$name.']]></string>',
            );

            $this->__replace($replacements, $this->_sources_dst . "/res/values-{$android_lang}/strings.xml");

        }

        if (file_exists($this->_sources_dst . '/res/values-fr/strings.xml')) {
            $this->__replace($replacements, $this->_sources_dst . '/res/values-fr/strings.xml');
        }

        $version = explode(".", $this->getDevice()->getVersion());
        $version_code = !empty($version[0]) ? $version[0] : 1;
        $version_name = !empty($version[0]) && !empty($version[1]) ? $version[0].".".$version[1] : "1.0";

        if($version_code != 1 || $version_name != "1.0") {
            $replacements = array(
                "versionCode 1" => "versionCode {$version_code}",
                'versionName "1.0"' => 'versionName "'.$version_name.'"',
            );

            $this->__replace($replacements, $this->_sources_dst."/../../build.gradle");
            $this->__replace($replacements, $this->_sources_dst."/../../build.gradle.save");

        }

        return $this;

    }

    protected function _copyImages() {

        $application = $this->getApplication();
        $icons = array(
            $this->_sources_dst.'/res/drawable-mdpi/app_icon.png'    => $application->getIcon(48, null, true),
            $this->_sources_dst.'/res/drawable-mdpi/push_icon.png'   => $application->getIcon(24, null, true),
            $this->_sources_dst.'/res/drawable-hdpi/app_icon.png'    => $application->getIcon(72, null, true),
            $this->_sources_dst.'/res/drawable-hdpi/push_icon.png'   => $application->getIcon(36, null, true),
            $this->_sources_dst.'/res/drawable-xhdpi/app_icon.png'   => $application->getIcon(96, null, true),
            $this->_sources_dst.'/res/drawable-xhdpi/push_icon.png'  => $application->getIcon(48, null, true),
            $this->_sources_dst.'/res/drawable-xxhdpi/app_icon.png'  => $application->getIcon(144, null, true),
            $this->_sources_dst.'/res/drawable-xxhdpi/push_icon.png' => $application->getIcon(72, null, true),
            $this->_dst.'/app_icon.png' => $application->getIcon(512, null, true),
        );

        $startup_images = array(
            "mdpi" => array(
                "old" => "startup_image.png",
                "new" => $application->getStartupImageUrl("standard", "base")
            ),
            "hdpi" => array(
                "old" => "startup_image.png",
                "new" => $application->getStartupImageUrl("standard", "base")
            ),
            "xhdpi" => array(
                "old" => "startup_image.jpg",
                "new" => $application->getStartupImageUrl("iphone_6", "base")
            ),
            "xxhdpi" => array(
                "old" => "startup_image.jpg",
                "new" => $application->getStartupImageUrl("iphone_6_plus", "base")
            )
        );

        foreach($startup_images as $density => $startup_image) {
            $oldfile = $startup_image['old'];
            unlink($this->_sources_dst."/res/drawable-$density/$oldfile");

            $newfile = $startup_image['new'];

            $extension = pathinfo($newfile, PATHINFO_EXTENSION);
            $icons[$this->_sources_dst."/res/drawable-$density/startup_image.$extension"] = $newfile;
        }


        foreach($icons as $icon_dst => $icon_src) {
            if(Core_Model_Lib_Image::getMimeType($icon_src) != 'image/png') {
                list($width, $height) = getimagesize($icon_src);
                $newStartupImage = imagecreatetruecolor($width, $height);
                $startupSrc = imagecreatefromstring(file_get_contents($icon_src));
                imagecopyresized($newStartupImage, $startupSrc, 0, 0, 0, 0, $width, $height, $width, $height);
                imagepng($newStartupImage, $icon_dst);
            } else {
                if(!copy($icon_src, $icon_dst)) {
                    throw new Exception(__('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
                }
            }
        }

        return $this;
    }

    protected function _zipFolder() {

        $src = $this->_dst;
        $filepath = $src.'/'.$this->_zipname.'.zip';

        Core_Model_Directory::zip($src, $filepath);

        if(!file_exists($filepath)) {
            throw new Exception('An error occurred during the creation of the archive ('.$filepath.')');
        }

        return $filepath;

    }

    protected function _generateApk() {

        $output = array();
        $alias = $this->getDevice()->getAlias();
        $store_password = $this->getDevice()->getStorePass();
        $key_password = $this->getDevice()->getKeyPass();
        $src = "var/tmp/applications/android/{$this->_folder_name}/Siberian";

        // Generates the keystore
        $keystore_base_path = Core_Model_Directory::getBasePathTo('var/apps/android/keystore/'.$this->getApplication()->getId().'.pks');
        if(!file_exists($keystore_base_path)) {
            $organization = preg_replace('/[,\s]+/', " ", System_Model_Config::getValueFor("company_name"));
            if (!$organization) $organization = "Default";
            exec('keytool -genkey -noprompt -alias ' . $alias . ' -dname "CN=' . $organization . ', O=' . $organization . '" -keystore ' . $keystore_base_path . ' -storepass ' . $store_password . ' -keypass ' . $key_password . ' -validity 36135 2>&1', $output);
        }
        
        // Adds the URL called at the end of the gradlew
        $gradlew_path = Core_Model_Directory::getBasePathTo("$src/app/gradlew");
        $gradlew_content = file_get_contents($gradlew_path);
        $url = Core_Model_Url::create("application/device/apkisgenerated", array("app_name" => $this->_folder_name));
        $gradlew_content .= 'wget "'.$url.'"';
        file_put_contents($gradlew_path, $gradlew_content);

        // Sets the Android SDK path
        $data = 'sdk.dir=' . Core_Model_Directory::getBasePathTo("var/apps/android/sdk");
        file_put_contents("$src/local.properties", $data);

        // Updates the build.gradle
        $content = file_get_contents("$src/app/build.gradle.save");
        $arraySearch = array('my_storePassword', 'my_keyAlias', 'my_keyPassword', 'my_packageName', 'my_keystore_path');
        $arrayReplace = array($store_password, $alias, $key_password, $this->_package_name, $keystore_base_path);
        $content = str_replace($arraySearch, $arrayReplace, $content);
        file_put_contents("$src/app/build.gradle", $content);

        // Changes the current directory
        chdir(Core_Model_Directory::getBasePathTo("$src/app"));

        // Creates a environment variable
        putenv('GRADLE_USER_HOME=' . Core_Model_Directory::getBasePathTo("var/tmp/applications/android/gradle"));

        // Executes gradlew
        $db = Zend_Registry::get('db');
        $db->closeConnection(); 
        exec('bash -l gradlew build 2>&1', $output);
        $db->getConnection(); 


        if (in_array('BUILD SUCCESSFUL', $output)) {
            return Core_Model_Directory::getBasePathTo("$src/app/build/outputs/apk/app-release.apk");
        } else {
            Zend_Registry::get("logger")->sendException(print_r($output, true), "apk_generation_", false);
            return false;
        }
    }


}
