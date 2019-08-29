<?php

use Siberian\File;

/**
 * Class Application_Model_Device_Ionic_Android
 */
class Application_Model_Device_Ionic_Android extends Application_Model_Device_Ionic_Android_Abstract
{

    /**
     * @var string
     */
    const VAR_FOLDER = "/var";

    /**
     * @var string
     */
    const BACKWARD_ANDROID = "/var/apps/android";

    /**
     * @var string
     */
    const IONIC_FOLDER = "/var/apps/ionic";

    /**
     * @var string
     */
    const SOURCE_FOLDER = "/var/apps/ionic/android";

    /**
     * @var string
     */
    const DEST_FOLDER = "/var/tmp/applications/ionic/android/%s";

    /**
     * @var string
     */
    const ARCHIVE_FOLDER = "/var/tmp/applications/ionic";

    /**
     * @var Application_Model_Application
     */
    public $app;

    /**
     * @var string
     */
    protected $_current_version = '1.0';

    /**
     * @var string
     */
    protected $_folder_name = '';

    /**
     * @var string
     */
    protected $_formatted_bundle_name = '';

    /**
     * @var string
     */
    protected $_zipname;

    /**
     * @var string
     */
    protected $_package_name;

    /**
     * @var string
     */
    protected $_application_id;

    /** Folders */

    /**
     * @var string
     */
    protected $_orig_source;

    /**
     * @var string
     */
    protected $_orig_source_src;

    /**
     * @var string
     */
    protected $_orig_source_res;

    /**
     * @var string
     */
    protected $_dest_source;

    /**
     * @var string
     */
    protected $_dest_source_src;

    /**
     * @var string
     */
    protected $_dest_source_res;

    /**
     * @var string
     */
    protected $_dest_archive;

    /**
     * Application_Model_Device_Ionic_Android constructor.
     * @param array $data
     * @throws Zend_Exception
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->_os_name = 'android';
        $this->_logger = Zend_Registry::get('logger');
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->_current_version;
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return 'Google Play';
    }

    /**
     * @return string
     */
    public function getBrandName()
    {
        return 'Google';
    }

    /**
     * @return array|mixed|string
     * @throws Exception
     * @throws Zend_Controller_Request_Exception
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function prepareResources()
    {
        $this->app = $this->getApplication();

        $this->_package_name = $this->app->getPackageName();
        $this->_application_id = Core_Model_Lib_String::format(
            $this->app->getName() . '_' . $this->app->getId(), true);
        $this->_application_name = $this->app->getName();

        // Prepping paths!
        $this->_generatePasswords();
        $this->_preparePathsVars();
        $this->_prepareRequest();
        $this->_cpFolder();
        $this->_cleanAssets();

        // Shared method!
        $this->ionicResources($this->app);
        $this->androidManifest();
        $this->renameMainPackage();
        $this->setStrings();

        $this->_prepareUrl();
        $this->_copyGoogleService();
        $this->_prepareLanguages();
        $this->_prepareGoogleAppId();

        if ($this->getDevice()->getDownloadType() !== 'apk') {

            if (defined('IS_APK_SERVICE')) {
                $queue = Application_Model_SourceQueue::getApkServiceStatus($this->app->getId());
                $keystore = $this->_prepareApk();

                $zip = $this->zipFolder();

                $basePath = Core_Model_Directory::getBasePathTo("");
                $zipPath = str_replace($basePath, '', $zip);

                $jobUrl = sprintf("https://%s/%s",
                    $queue['host'],
                    $zipPath);

                $buildType = __get('apk_build_type') === 'debug' ? 'cdvBuildDebug' : 'cdvBuildRelease';

                Siberian_Request::get(
                    "https://jenkins-prod02.xtraball.com/job/apk-generator/buildWithParameters",
                    [
                        'token' => 'NZDMeOBA2SLM8KyJtApAQbrN6Oy9dg6m',
                        'jobUrl' => base64_encode($jobUrl),
                        'jobName' => base64_encode($this->_application_id),
                        'license' => base64_encode(__get('siberiancms_key')),
                        'appId' => base64_encode($this->app->getId()),
                        'appName' => $this->_application_id,
                        'uuid' => uniqid(),
                        'buildType' => $buildType,
                        'keystore' => base64_encode(json_encode($keystore))
                    ],
                    null,
                    [
                        'type' => 'basic',
                        'username' => 'ios-builder',
                        'password' => 'ced2eb561db43afb09c633b8f68c1f17',
                    ]);

                if (!in_array(Siberian_Request::$statusCode, [100, 200, 201])) {
                    throw new \Siberian\Exception(__('Cannot send APK build to service %s.',
                        Siberian_Request::$statusCode));
                }
            } else {
                $this->_prepareApk();

                // Check
                $keystoreFilename = $this->app->getId() . '.pks';
                $keystorePath = Core_Model_Directory::getBasePathTo(self::BACKWARD_ANDROID . '/keystore/' . $keystoreFilename);

                if (!is_file($keystorePath)) {
                    $releaseSigning = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/release-signing.properties");
                    unlink($releaseSigning);
                }

                $zip = $this->zipFolder();
            }

            return $zip;
        }

        return $this->_generateApk();
    }

    /**
     *
     */
    protected function _preparePathsVars()
    {
        $this->_folder_name = $this->getDevice()->getTmpFolderName();

        /** Ionic sources */
        $this->_orig_source = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER);
        $this->_orig_source_src = $this->_orig_source . '/app/src/main/java';
        $this->_orig_source_res = $this->_orig_source . '/app/src/main/res';

        /** /var/tmp/applications/[DESIGN]/[PLATFORM]/[APP_NAME] */
        $this->_dest_source = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER);
        $this->_dest_source = sprintf($this->_dest_source, $this->_folder_name);
        $this->_dest_source_src = $this->_dest_source . '/app/src/main/java';
        $this->_dest_source_res = $this->_dest_source . '/app/src/main/res';
        $this->_dest_source_package_default = $this->_dest_source_src . '/' . $this->_default_bundle_path;
        $this->_dest_source_package = $this->_dest_source_src . '/' .
            str_replace('.', '/', $this->_package_name);

        $this->_dest_archive = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER);

        /** Vars */
        $this->_zipname = sprintf(
            "%s_%s_%s",
            $this->getDevice()->getAlias(),
            $this->app->getId(),
            'android_source');
    }

    /**
     * @return $this
     */
    protected function _generatePasswords()
    {
        $save = false;
        $device = $this->getDevice();
        $passwords = [
            'store_pass' => $device->getStorePass(),
            'key_pass' => $device->getKeyPass(),
            'alias' => $device->getAlias(),
        ];

        if (empty($passwords['store_pass'])) {
            $store_pass = Core_Model_Lib_String::generate(8);
            $device->setStorePass($store_pass);
            $passwords['store_pass'] = $store_pass;
            $save = true;
        }
        if (empty($passwords['key_pass'])) {
            $key_pass = Core_Model_Lib_String::generate(8);
            $device->setKeyPass($key_pass);
            $passwords['key_pass'] = $key_pass;
            $save = true;
        }
        if (empty($passwords['alias'])) {
            $alias = Core_Model_Lib_String::format($this->app->getName(), true);
            if (!$alias) $alias = $this->app->getId();

            $device->setAlias($alias);
            $passwords['alias'] = $alias;

            $save = true;
        }

        if ($save) {
            $path_android = Core_Model_Directory::getBasePathTo('var/apps/android');
            if (!file_exists("{$path_android}/pwd")) {
                mkdir("{$path_android}/pwd", 0775);
            }
            File::putContents("{$path_android}/pwd/app_{$device->getAppId()}.txt", print_r($passwords, true));
            $this->_logger->sendException(print_r($passwords, true), "android_pwd_{$device->getAppId()}_", false);
            $device->save();
        }

        return $this;

    }

    /**
     * @throws Zend_Controller_Request_Exception
     */
    protected function _prepareRequest()
    {
        if (!defined('CRON')) {
            $request = new Siberian_Controller_Request_Http($this->app->getUrl());
            $request->setPathInfo();
            $this->_request = $request;
        }
    }

    /**
     * @return $this
     */
    protected function _cpFolder()
    {
        /** Supprime le dossier s'il existe puis le créé */
        if (is_dir($this->_dest_source)) {
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

    /**
     * @return $this
     */
    protected function _cleanAssets()
    {
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/css'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/controllers'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/directives'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/factory'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/features'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/filters'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/libraries'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/providers'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/js/services'");
        exec("rm -f '{$this->_dest_source}/app/src/main/assets/www/js/MusicControls.js'");
        exec("rm -f '{$this->_dest_source}/app/src/main/assets/www/js/app.js'");
        exec("rm -f '{$this->_dest_source}/app/src/main/assets/www/js/utils/features.js'");
        exec("rm -f '{$this->_dest_source}/app/src/main/assets/www/js/utils/form-post.js'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/lib/ionic/css'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/lib/ionic/js'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/lib/ionic/scss'");
        exec("rm -f '{$this->_dest_source}/app/src/main/assets/www/lib/ionic/version.json'");
        exec("rm -Rf '{$this->_dest_source}/app/src/main/assets/www/templates'");

        return $this;
    }

    /**
     *
     */
    protected function _prepareUrl()
    {
        $application = $this->getApplication();
        $protocol = "https://";
        if (defined('CRON')) {
            $domain = $this->getDevice()->getHost();
        } else {
            $domain = $this->_request->getHttpHost();
        }

        $appKey = $application->getKey();
        $disableBatteryOptimization = (boolean) filter_var($application->getDisableBatteryOptimization(), FILTER_VALIDATE_BOOLEAN);
        $_dbo = $disableBatteryOptimization ? "true" : "false";

        $url_js_content = "
/** Auto-generated url.js */
var REDIRECT_URI = false;
var IS_NATIVE_APP = true;
var DEVICE_TYPE = 1;
window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, \"\");
var CURRENT_LANGUAGE = AVAILABLE_LANGUAGES.indexOf(language) >= 0 ? language : 'en';
var DISABLE_BATTERY_OPTIMIZATION = {$_dbo};

// WebView
if (typeof IS_PREVIEW === 'undefined' ||
    (typeof IS_PREVIEW !== 'undefined' && IS_PREVIEW !== true)) {
    PROTOCOL = '{$protocol}';
    DOMAIN = '{$protocol}{$domain}';
    APP_KEY = '{$appKey}';
    BASE_PATH = '/'+APP_KEY;
}

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + '/';";

        File::putContents($this->_dest_source . '/app/src/main/assets/www/js/utils/url.js', $url_js_content);
    }

    /**
     * @throws Zend_Exception
     */
    protected function _copyGoogleService()
    {
        $credentials = (new Push_Model_Firebase())
            ->find(0, 'admin_id');

        $googleService = json_decode($credentials->getGoogleService(), true);

        $googleService['client'][0]['client_info']['android_client_info']['package_name'] = $this->_package_name;

        $jsonConfig = json_encode($googleService, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        File::putContents($this->_dest_source . '/app/google-services.json', $jsonConfig);
    }

    /**
     *
     */
    protected function _prepareLanguages()
    {
        $languages = array_keys(Core_Model_Language::getLanguages());

        $file_content = "
/** Auto-generated languages.js */
var AVAILABLE_LANGUAGES = new Array('" . implode("','", $languages) . "');

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

        File::putContents($this->_dest_source . "/app/src/main/assets/www/js/utils/languages.js", $file_content);
    }

    /**
     * @throws Exception
     */
    protected function _prepareGoogleAppId()
    {

        $googlekey = Push_Model_Certificate::getAndroidKey();

        $googleappid = "<string name=\"google_app_id\">{$googlekey}</string>";

        $replacements = ["<string name=\"google_app_id\">01234567890</string>" => $googleappid];

        $this->__replace($replacements, $this->_dest_source_res . "/values/strings.xml");
    }

    /**
     * @return array
     */
    public function _prepareApk ()
    {
        $device = $this->getDevice();
        $alias = $device->getAlias();
        $storepass = $device->getStorePass();
        $keypass = $device->getKeyPass();

        // Sanitize organization name, or default if empty!
        $organization = preg_replace('/[,\s\']+/', ' ', __get('company_name'));
        if (!$organization) {
            $organization = 'Default';
        }

        // Generating Keystore (or not)!
        $keystoreFilename = $this->app->getId() . '.pks';
        $keystorePath = Core_Model_Directory::getBasePathTo(self::BACKWARD_ANDROID . '/keystore/' . $keystoreFilename);
        if (!is_dir(dirname($keystorePath))) {
            mkdir(dirname($keystorePath), 0777, true);
        }
        if (!file_exists($keystorePath)) {
            $keystore = [
                'generate' => true,
                'alias' => $alias,
                'organization' => $organization,
                'storepass' => $storepass,
                'keypass' => $keypass,
            ];
        } else {
            $keystore = [
                'generate' => false,
                'alias' => $alias,
                'organization' => $organization,
                'storepass' => $storepass,
                'keypass' => $keypass,
            ];

            // Copy the existing pks before archiving!
            copy($keystorePath, $this->_dest_source . '/keystore.pks');
        }

        // Signing informations!
        $releaseSigning = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/release-signing.properties");
        $signing = "keyAlias={$alias}
keyPassword={$keypass}
storeFile=keystore.pks
storePassword={$storepass}";
        File::putContents($releaseSigning, $signing);

        return $keystore;
    }

    /**
     * @param bool $cron
     * @return array
     * @throws Exception
     * @throws Zend_Exception
     */
    protected function _generateApk($cron = false)
    {
        // Replace JAVA_HOME if set in config.php priority is given to "system_config"
        $configJavaHome = __get('java_home');
        $javaHomeOpts = '';
        if ($configJavaHome !== false) {
            $javaHomeOpts = 'export JAVA_HOME="' . $configJavaHome . '"' . "\n";
            $javaHomeOptsKeytool = "export JAVA_HOME='$configJavaHome';";
        }

        // Fallback with value from config.php if existing
        $configJavaHome = __getConfig('java_home');
        if ($configJavaHome !== false) {
            $javaHomeOpts = 'export JAVA_HOME="' . $configJavaHome . '"' . "\n";
            $javaHomeOptsKeytool = "export JAVA_HOME='$configJavaHome';";
        }

        /** Fetching vars. */
        $output = [];

        /** Needed for build permissions etc ... */
        exec("chmod -R 777 {$this->_dest_source}");

        /** Setting up Android SDK */
        $ionic_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER);
        $tools_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER . "/tools");
        $android_sdk_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER . "/tools/android-sdk");
        $keystore_folder = Core_Model_Directory::getBasePathTo(self::BACKWARD_ANDROID . "/keystore");
        $var_log = Core_Model_Directory::getBasePathTo(self::VAR_FOLDER . "/log");
        $var_path = Core_Model_Directory::getBasePathTo(self::VAR_FOLDER);
        $gradle_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER . "/tools/gradle");


        /** Damn licenses */
        $licenses_folder = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER . "/tools/android-sdk/licenses");
        if (!file_exists($licenses_folder)) {
            mkdir($licenses_folder, 0777, true);
        }
        if (!file_exists($licenses_folder . "/android-sdk-license")) {
            File::putContents($licenses_folder . "/android-sdk-license", "\n8933bad161af4178b1185d1a37fbf41ea5269c55");
            chmod($licenses_folder . "/android-sdk-license", 0777);
        }

        /** Joker /!\ */
        exec("chmod -R 777 {$var_path}");

        /** Checking write permissions */
        $files_to_test = [$ionic_path, $tools_path, $keystore_folder];
        foreach ($files_to_test as $file) {
            if (file_exists($file)) {
                if (!is_writable($file) && chmod($file, 0777) === false) {
                    $output[] = "Folder is not writable {$file}";
                    $this->_logger->sendException(print_r($output, true), "apk_generation_", false);
                }
            } else {
                mkdir($file, 0777, true);
            }
        }

        $alias = $this->getDevice()->getAlias();
        $app_id = $this->app->getId();

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
            // keytool alias companyName keystorePath storePassword keyPassword

            chmod($keytool, 0777);
            exec("{$javaHomeOptsKeytool} {$keytool} '{$alias}' '{$organization}' '{$keystore_path}' '{$store_password}' '{$key_password}'");

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

        $javaOptions = __get('java_options');
        if (empty($javaOptions)) {
            $javaOptions = '-Xmx384m -Xms384m -XX:MaxPermSize=384m';
        }

        $gradleOptions = __get('gradle_options');
        if (empty($gradleOptions)) {
            $gradleOptions = '-Dorg.gradle.daemon=true';
        }

        /** Adding a call to the sdk-updater.php at the gradlew top */
        $search = "DEFAULT_JVM_OPTS=\"\"";
        $replace = $javaHomeOpts . "      
export _JAVA_OPTIONS=\"$javaOptions\"
export ANDROID_HOME=\"$android_sdk_path\"
export GRADLE_USER_HOME=\"$gradle_path\"
export GRADLE_HOME=\"$gradle_path\"
export GRADLE_OPTS=\"$gradleOptions\"

DEFAULT_JVM_OPTS=\"\"
";

        $this->__replace([$search => $replace], $gradlew_path);

        $android_sdk = "sdk.dir={$android_sdk_path}";

        $local_properties_path = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/local.properties");
        File::putContents($local_properties_path, $android_sdk);
        $local_properties_path_cordova = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/CordovaLib/local.properties");
        File::putContents($local_properties_path_cordova, $android_sdk);

        /** Signing informations */
        $release_signing_gradle_path = Core_Model_Directory::getBasePathTo("{$this->_dest_source}/release-signing.properties");
        $signing = "keyAlias={$alias}
keyPassword={$key_password}
storeFile={$keystore_filename}
storePassword={$store_password}";

        // Controlling release signing.
        File::putContents($release_signing_gradle_path, $signing);

        // Change current directory
        $projectSourcePath = Core_Model_Directory::getBasePathTo("{$this->_dest_source}");
        chdir($projectSourcePath);

        // Creating ENV PATH
        $gradle_path = Core_Model_Directory::getBasePathTo(self::IONIC_FOLDER . "/tools/gradle");
        putenv("GRADLE_USER_HOME={$gradle_path}");
        putenv("GRADLE_HOME={$gradle_path}");
        putenv("ANDROID_HOME={$android_sdk_path}");

        // Replace JAVA_HOME if set in config.php
        $configJaveHome = __getConfig('java_home');
        if ($configJaveHome !== false) {
            putenv('JAVA_HOME=' . $configJaveHome);
        }

        // Executing gradlew!
        if (file_exists($var_log)) {
            unlink($var_log);
        }

        // APK Build type!
        $buildType = 'cdvBuildRelease';
        switch (System_Model_Config::getValueFor('apk_build_type')) {
            case 'debug':
                $buildType = 'cdvBuildDebug';
                break;
            default:
                $buildType = 'cdvBuildRelease';
        }

        // We restart connection to be sure MySQL is still answering!
        $db = Zend_Registry::get('db');
        $db->closeConnection();

        $sdkManager = Core_Model_Directory::getBasePathTo('/var/apps/ionic/tools/sdkmanager.php');

        // /Require sdk manager to start!
        require_once $sdkManager;
        // !End sdk manager!

        chdir($projectSourcePath);
        exec("bash -l gradlew " . $buildType . " 2>&1", $output);
        $db->getConnection();

        $result = implode('', $output);
        if (!defined("CRON")) {
            if (strpos($result, 'BUILD SUCCESSFUL') === false) {
                $this->_logger->sendException(print_r($output, true), "apk_generation_", false);
                return false;
            }
            exit('Done ...');
        } else {
            $success = (strpos($result, 'BUILD SUCCESSFUL') !== false);
            $apkBasePathRelease = "{$this->_dest_source}/app/build/outputs/apk/release/app-release.apk";
            $apkBasePathDebug = "{$this->_dest_source}/app/build/outputs/apk/debug/app-debug.apk";

            if (is_readable($apkBasePathRelease)) {
                $targetPath = Core_Model_Directory::getBasePathTo("var/tmp/applications/ionic/") . "{$this->_folder_name}-release.apk";
                rename($apkBasePathRelease, $targetPath);
            } else if (is_readable($apkBasePathDebug)) {
                $targetPath = Core_Model_Directory::getBasePathTo("var/tmp/applications/ionic/") . "{$this->_folder_name}-debug.apk";
                rename($apkBasePathDebug, $targetPath);
            }

            # Clean-up dest files.
            $logFailed = Core_Model_Directory::getBasePathTo("var/log/apk_fail_{$this->_folder_name}.log");
            if (!$success) {
                File::putContents($logFailed, implode("\n", $output));
            }

            return [
                'success' => $success,
                'log' => $output,
                'path' => is_readable($targetPath) ? $targetPath : false,
            ];
        }

    }
}
