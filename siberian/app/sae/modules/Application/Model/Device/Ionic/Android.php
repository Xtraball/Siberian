<?php

class Application_Model_Device_Ionic_Android extends Application_Model_Device_Ionic_Android_Abstract {

    const VAR_FOLDER = "/var";
	const BACKWARD_ANDROID = "/var/apps/android";
	const IONIC_FOLDER = "/var/apps/ionic";
    const SOURCE_FOLDER = "/var/apps/ionic/android";
    const DEST_FOLDER = "/var/tmp/applications/ionic/android/%s";
    const ARCHIVE_FOLDER = "/var/tmp/applications/ionic";

    protected $_current_version = '1.0';
    protected $_folder_name = '';
    protected $_formatted_bundle_name = '';
    protected $_zipname;
    protected $_package_name;
    protected $_application_id;
    /** Folders */
    protected $_orig_source;
    protected $_orig_source_src;
    protected $_orig_source_res;
    protected $_dest_source;
    protected $_dest_source_src;
    protected $_dest_source_res;
    protected $_dest_archive;

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_os_name = "android";
        $this->_logger = Zend_Registry::get("logger");
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

    /**
     * @param bool $cron
     * @return bool|string
     * @throws Exception
     */
    public function prepareResources() {

        $this->_application = $this->getApplication();

        $this->_package_name = $this->_application->getPackageName();
        $this->_application_id = Core_Model_Lib_String::format($this->_application->getName()."_".$this->_application->getId(), true);
        $this->_application_name = $this->_application->getName();

        /** Prepping paths */
        $this->_generatePasswords();
        $this->_preparePathsVars();
        $this->_prepareRequest();
        $this->_cpFolder();
        $this->_prepareFiles();

        /** Shared method */
        $this->ionicResources($this->_application);
        $this->androidManifest();
        $this->renameMainPackage();
        $this->gradleConfig();
        $this->setStrings();

        $this->_prepareUrl();
        $this->_prepareLanguages();
        $this->_admob();
        $this->_prepareGoogleAppId();

        if($this->getDevice()->getDownloadType() != "apk") {
            $zip = $this->zipFolder();
            return $zip;
        }

        if($apk = $this->_generateApk()) {
            return $apk;
        }

    }
    
    protected function _preparePathsVars() {
        $this->_folder_name = $this->getDevice()->getTmpFolderName();

        /** Ionic sources */
        $this->_orig_source = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER);
        $this->_orig_source_src = $this->_orig_source."/src";
        $this->_orig_source_res = $this->_orig_source."/res";

        /** /var/tmp/applications/[DESIGN]/[PLATFORM]/[APP_NAME] */
        $this->_dest_source = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER);
        $this->_dest_source = sprintf($this->_dest_source, $this->_folder_name);
        $this->_dest_source_src = $this->_dest_source."/src";
        $this->_dest_source_res = $this->_dest_source."/res";
        $this->_dest_source_package_default = $this->_dest_source_src."/".$this->_default_bundle_path;
        $this->_dest_source_package = $this->_dest_source_src.'/'.str_replace(".", "/", $this->_package_name);

        $this->_dest_archive = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER);

        /** Vars */
        $this->_zipname = sprintf("%s_%s_%s", $this->getDevice()->getAlias(), $this->_application->getId(), "android_source");
    }

    /** @STEP ok */
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
            $alias = Core_Model_Lib_String::format($this->_application->getName(), true);
            if(!$alias) $alias = $this->_application->getId();

            $device->setAlias($alias);
            $passwords["alias"] = $alias;

            $save = true;
        }

        if($save) {
            $path_android = Core_Model_Directory::getBasePathTo("var/apps/android");
            if(!file_exists("{$path_android}/pwd")) {
                mkdir("{$path_android}/pwd", 0775);
            }
            file_put_contents("{$path_android}/pwd/app_{$device->getAppId()}.txt", print_r($passwords, true));
            $this->_logger->sendException(print_r($passwords, true), "android_pwd_{$device->getAppId()}_", false);
            $device->save();
        }

        return $this;

    }

    protected function _prepareRequest() {
        if(!defined("CRON")) {
            $request = new Siberian_Controller_Request_Http($this->_application->getUrl());
            $request->setPathInfo();
            $this->_request = $request;
        }
    }

    protected function _cpFolder() {

        /** Supprime le dossier s'il existe puis le créé */
        if(is_dir($this->_dest_source)) {
            Core_Model_Directory::delete($this->_dest_source);
        }
        mkdir($this->_dest_source, 0777, true);

        /** Copie les sources */
        Core_Model_Directory::duplicate($this->_orig_source, $this->_dest_source);

        /** Rename com.* package */
        Core_Model_Directory::move($this->_dest_source_package_default, $this->_dest_source_package);
        Core_Model_Directory::delete($this->_dest_source_package_default);

        /** Needed for build permissions etc ... */
        exec("chmod -R 777 {$this->_dest_source}");

        return $this;
    }

    /** @STEP in progress */
    protected function _prepareFiles() {

        /** Static push.js senderID (Only for apps) */
        $senderID = Push_Model_Certificate::getAndroidSenderId();

        # Empty senderID cause malformed JSON in Android
        $senderID = trim($senderID);
        if(!empty($senderID)) {
            $senderIdReplacements = array(
                'senderID: "01234567890"' => 'senderID: "' . $senderID . '"',
            );
            $this->__replace($senderIdReplacements, $this->_dest_source."/assets/www/js/factory/push.js");
        }

        return $this;

    }

    /** @TODO remove default langage */
    protected function _prepareUrl() {

        if(defined("CRON")) {
            $protocol = System_Model_Config::getValueFor("use_https") ? 'https://' : 'http://';
            $domain = $this->getDevice()->getHost();
        } else {
            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $domain = $this->_request->getHttpHost();
        }

        $app_key = $this->getApplication()->getKey();

        $url_js_content = "
/** Auto-generated url.js */
var REDIRECT_URI = false;
var IS_NATIVE_APP = true;
var DEVICE_TYPE = 1;
window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, \"\");
var CURRENT_LANGUAGE = AVAILABLE_LANGUAGES.indexOf(language) >= 0 ? language : 'en';
DOMAIN = '{$protocol}{$domain}';
APP_KEY = '{$app_key}';
BASE_PATH = '/'+APP_KEY;

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + '/';";

        file_put_contents($this->_dest_source."/assets/www/js/utils/url.js", $url_js_content);

        /** Embed CSS */
        //$app_id = $this->getApplication()->getId();
        //$base_css = Core_Model_Directory::getBasePathTo("var/cache/css/{$app_id}.css");
        //if(is_readable($base_css)) {
        //    file_put_contents($this->_dest_source."/assets/www/css/app.css", file_get_contents($base_css));
        //}

    }

    protected function _prepareLanguages() {

        $languages = array_keys(Core_Model_Language::getLanguages());

        $file_content = "
/** Auto-generated languages.js */
var AVAILABLE_LANGUAGES = new Array('".implode("','", $languages)."');

/**
 * Find navigator preferred language
 */
var language = \"en\";
if(navigator.language) {
    var tmp_language = navigator.language.replace(\"-\", \"_\");

    try {
        if(AVAILABLE_LANGUAGES.indexOf(tmp_language) >= 0) {
            language = tmp_language
        } else {
            language = tmp_language.split(\"_\")[0];
        }
    } catch(e) {
        language = \"en\";
    }
}";

        file_put_contents($this->_dest_source."/assets/www/js/utils/languages.js", $file_content);

    }

    protected function _prepareGoogleAppId() {

        $googlekey = Push_Model_Certificate::getAndroidKey();

        $googleappid = "<string name=\"google_app_id\">{$googlekey}</string>";

        $replacements = array("<string name=\"google_app_id\">01234567890</string>" => $googleappid);

        $this->__replace($replacements, $this->_dest_source_res."/values/strings.xml");

    }

    /** @TODO alot to refresh here
     *
     * CALL ME BAD OLD COMPILER
     *  .
     */
    protected function _generateApk($cron = false) {

    	/** Fetching vars. */
    	$output = array();

        /** Needed for build permissions etc ... */
        exec("chmod -R 777 {$this->_dest_source}");

        /** Setting up Android SDK */
        $ionic_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER);
        $tools_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER."/tools");
        $android_sdk_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER."/tools/android-sdk");
        $keystore_folder = Core_Model_Directory::getBasePathTo(self::BACKWARD_ANDROID."/keystore");
        $var_log = Core_Model_Directory::getBasePathTo(self::VAR_FOLDER."/log");
        $var_path = Core_Model_Directory::getBasePathTo(self::VAR_FOLDER);
        $gradle_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER."/tools/gradle");


        /** Damn licenses */
        $licenses_folder = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER."/tools/android-sdk/licenses");
        if(!file_exists($licenses_folder)) {
            mkdir($licenses_folder, 0777, true);
        }
        if(!file_exists($licenses_folder . "/android-sdk-license")) {
            file_put_contents($licenses_folder . "/android-sdk-license", "\n8933bad161af4178b1185d1a37fbf41ea5269c55");
            chmod($licenses_folder . "/android-sdk-license", 0777);
        }

        /** Joker /!\ */
        exec("chmod -R 777 {$var_path}");

        /** Checking write permissions */
        $files_to_test = array($ionic_path, $tools_path, $keystore_folder);
        foreach($files_to_test as $file) {
            if(file_exists($file)) {
                if(!is_writable($file) && chmod($file, 0777) === false) {
                    $output[] = "Folder is not writable {$file}";
                    $this->_logger->sendException(print_r($output, true), "apk_generation_", false);
                }
            }
            else {
                mkdir($file, 0777, true);
            }
        }

    	$alias = $this->getDevice()->getAlias();
    	$app_id = $this->_application->getId();

    	$store_password = $this->getDevice()->getStorePass();
    	$key_password = $this->getDevice()->getKeyPass();

    	// Generating Keystore!
        $keystore_filename = "{$app_id}.pks";
    	$keystore_path = Core_Model_Directory::getBasePathTo(self::BACKWARD_ANDROID . '/keystore/' . $keystore_filename);
    	if (!file_exists($keystore_path)) {
    		// Sanitize organization name, or default if empty!
    		$organization = preg_replace('/[,\s\']+/', ' ', System_Model_Config::getValueFor('company_name'));
    		if (!$organization) {
    			$organization = 'Default';
    		}

    		$keytool = Core_Model_Directory::getBasePathTo('app/sae/modules/Application/Model/Device/Ionic/bin/helper');

    		chmod($keytool, 0777);
    		exec("{$keytool} '{$alias}' '{$organization}' '{$keystore_path}' '{$store_password}' '{$key_password}'");

    		// Copy PKS with a uniqid/date somewhere else!
    		copy($keystore_path, str_replace('.pks', date('d-m-Y') . '-' . uniqid() . '.pks', $keystore_path));
    	}
        // Copy the keystore locally!
        copy($keystore_path, "{$this->_dest_source}/{$keystore_filename}");

        // Backup PKS in DB!
        $pks_content = $this->getDevice()->getPks();
        if (empty($pks_content)) {
            $pks_content = file_get_contents($keystore_path, FILE_BINARY);
            $this->getDevice()->setPks(bin2hex($pks_content))->save();
        }

    	// Gradle configuration!
    	$gradlew_path = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/gradlew");

        /** Adding a call to the sdk-updater.php at the gradlew top */
        /** @TODO */
        $search = "DEFAULT_JVM_OPTS=\"\"";
        $replace = "
export _JAVA_OPTIONS=\"-Xmx384m -Xms384m -XX:MaxPermSize=384m\"
export ANDROID_HOME=\"$android_sdk_path\"
export GRADLE_USER_HOME=\"$gradle_path\"
export GRADLE_HOME=\"$gradle_path\"

DEFAULT_JVM_OPTS=\"\"
";

        $this->__replace(array($search => $replace), $gradlew_path);

		$android_sdk = "sdk.dir={$android_sdk_path}";

		$local_properties_path = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/local.properties");
		file_put_contents($local_properties_path, $android_sdk);

    	/** Signing informations */
        $release_signing_gradle_path = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/release-signing.properties");
    	$signing = "keyAlias={$alias}
keyPassword={$key_password}
storeFile={$keystore_filename}
storePassword={$store_password}";

        /** Controlling release signing. */
        file_put_contents($release_signing_gradle_path, $signing);

    	/** Change current directory */
		$project_source_path = Core_Model_Directory::getBasePathTo("{$this->_dest_source}");
    	chdir($project_source_path);

    	/** Creating ENV PATH */
		$gradle_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER."/tools/gradle");
    	putenv("GRADLE_USER_HOME={$gradle_path}");
    	putenv("GRADLE_HOME={$gradle_path}");

        /** DEBUG OSX: it doesn't find "which java" with php, also the given path is generic and symlink to the latest version.  */
        $is_darwin = exec("uname");
        if(strpos($is_darwin, "arwin") !== false) {
            $java_home = getenv("JAVA_HOME");
            if(empty($java_home)) {
                putenv("JAVA_HOME=/System/Library/Frameworks/JavaVM.framework/Home");
            }
        }

    	/** Executing gradlew */
        $var_log = $var_log."/apk-build.log";
        if(file_exists($var_log)) {
            unlink($var_log);
        }

        //we restart connection to not have a "MYSQL GONE AWWAAYYYYY!!!! error"
        $db = Zend_Registry::get('db');
        $db->closeConnection(); 
    	exec("bash -l gradlew cdvBuildRelease 2>&1", $output);
        $db->getConnection(); 


        if(!defined("CRON")) {
            if (!in_array('BUILD SUCCESSFUL', $output)) {
                $this->_logger->sendException(print_r($output, true), "apk_generation_", false);
                return false;
            }
            exit('Done ...');
        } else {
            $success = in_array("BUILD SUCCESSFUL", $output);
            $apk_base_path = "{$this->_dest_source}/build/outputs/apk/{$this->_folder_name}-release.apk";

            if(is_readable($apk_base_path)) {
                $target_path = Core_Model_Directory::getBasePathTo("var/tmp/applications/ionic/")."{$this->_folder_name}-release.apk";
                rename($apk_base_path, $target_path);
            }

            # Clean-up dest files.
            //Core_Model_Directory::delete($this->_dest_source);
            $log_failed = Core_Model_Directory::getBasePathTo("var/log/apk_fail_{$this->_folder_name}.log");
            if(!$success) {
                file_put_contents($log_failed, implode("\n", $output));
            }

            return array(
                "success" => $success,
                "log" => $output,
                "path" => is_readable($target_path) ? $target_path : false,
            );
        }

    }



}
