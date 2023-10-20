<?php

use Siberian\File;
use Siberian\Hook\Source as HookSource;
use Siberian\Provider;
use Siberian\Request;

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
    public $_current_version = '1.0';

    /**
     * @var string
     */
    public $_folder_name = '';

    /**
     * @var string
     */
    public $_formatted_bundle_name = '';

    /**
     * @var string
     */
    public $_zipname;

    /**
     * @var string
     */
    public $_package_name;

    /**
     * @var string
     */
    public $_application_id;

    /** Folders */

    /**
     * @var string
     */
    public $_orig_source;

    /**
     * @var string
     */
    public $_orig_source_src;

    /**
     * @var string
     */
    public $_orig_source_res;

    /**
     * @var string
     */
    public $_dest_source;

    /**
     * @var string
     */
    public $_dest_source_src;

    /**
     * @var string
     */
    public $_dest_source_res;

    /**
     * @var string
     */
    public $_dest_archive;

    /**
     * @var string
     */
    public $admobAppIdentifier = 'ca-app-pub-0000000000000000~0000000000';

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

        // Hooks for modules to alter sources
        $beforeArchiveHooks = HookSource::getActionsBeforeArchive(HookSource::TYPE_ANDROID);
        foreach ($beforeArchiveHooks as $beforeArchiveHook) {
            try {
                $callback = $beforeArchiveHook['callback'];
                $callback($this);
            } catch (\Exception $e) {
                // throw $e;
                // Hooks are enclosed inside catch to be sure we don't break things
            }
        }


        if (defined('IS_APK_SERVICE')) {
            $queue = Application_Model_SourceQueue::getApkServiceStatus($this->app->getId());
            $keystore = $this->_prepareApk();

            $zip = $this->zipFolder();

            $basePath = path("");
            $zipPath = str_replace($basePath, '', $zip);

            $jobUrl = sprintf("https://%s/%s",
                $queue['host'],
                $zipPath);

            $buildType = __get('apk_build_type') === 'debug' ? 'cdvBuildDebug' : 'cdvBuildRelease';

            $buildUrl = Provider::getApkAabBuilder();

            Request::get(
                $buildUrl,
                [
                    'token' => 'NZDMeOBA2SLM8KyJtApAQbrN6Oy9dg6m',
                    'jobUrl' => base64_encode($jobUrl),
                    'jobName' => base64_encode($this->_application_id),
                    'license' => base64_encode(__get('siberiancms_key')),
                    'appId' => base64_encode($this->app->getId()),
                    'appName' => $this->_application_id,
                    'uuid' => uniqid('apk_', true),
                    'buildType' => $buildType,
                    'keystore' => base64_encode(json_encode($keystore)),
                    'withAab' => 'aab'
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

        } else { // Sources only
            $this->_prepareApk();

            // Check
            $keystoreFilename = $this->app->getId() . '.pks';
            $keystorePath = path(self::BACKWARD_ANDROID . '/keystore/' . $keystoreFilename);

            // Do not embed if not keystore is provided!
            if (!is_file($keystorePath)) {
                $releaseSigning = path("{$this->_dest_source}/release-signing.properties");
                unlink($releaseSigning);
            }

            $zip = $this->zipFolder();
        }

        return $zip;
    }

    /**
     *
     */
    protected function _preparePathsVars()
    {
        $this->_folder_name = $this->getDevice()->getTmpFolderName();

        $admobAppId = $this->getDevice()->getAdmobAppId();
        if (!empty($admobAppId)) {
            $this->admobAppIdentifier = trim($admobAppId);
        }

        /** Ionic sources */
        $this->_orig_source = path(self::SOURCE_FOLDER);
        $this->_orig_source_src = $this->_orig_source . '/app/src/main/java';
        $this->_orig_source_res = $this->_orig_source . '/app/src/main/res';

        /** /var/tmp/applications/[DESIGN]/[PLATFORM]/[APP_NAME] */
        $this->_dest_source = path(self::DEST_FOLDER);
        $this->_dest_source = sprintf($this->_dest_source, $this->_folder_name);
        $this->_dest_source_src = $this->_dest_source . '/app/src/main/java';
        $this->_dest_source_res = $this->_dest_source . '/app/src/main/res';
        $this->_dest_source_package_default = $this->_dest_source_src . '/' . $this->_default_bundle_path;
        $this->_dest_source_package = $this->_dest_source_src . '/' .
            str_replace('.', '/', $this->_package_name);

        $this->_dest_archive = path(self::ARCHIVE_FOLDER);

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
            $store_pass = generate_strong_password(10, ['special' => false]);
            $device->setStorePass($store_pass);
            $passwords['store_pass'] = $store_pass;
            $save = true;
        }
        if (empty($passwords['key_pass'])) {
            $key_pass = generate_strong_password(10, ['special' => false]);
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
            $path_android = path('var/apps/android');
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
        $languages = array_keys(Core_Model_Language::getLanguages());
        $version = \Siberian\Version::VERSION;

        $url_js_content = "
/** Auto-generated url.js */
var REDIRECT_URI = false;
var IS_NATIVE_APP = true;
var DEVICE_TYPE = 1;
var XS_VERSION = '{$version}';
window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, \"\");
var AVAILABLE_LANGUAGES = ['" . implode_polyfill("','", $languages) . "'];
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
    }

    /**
     *
     */
    protected function _prepareLanguages()
    {
        // deprecated
    }

    /**
     * @throws Exception
     */
    protected function _prepareGoogleAppId()
    {

        //$googlekey = Push_Model_Certificate::getAndroidKey();
//
        //$googleappid = "<string name=\"google_app_id\">{$googlekey}</string>";
//
        //$replacements = ["<string name=\"google_app_id\">01234567890</string>" => $googleappid];
//
        //$this->__replace($replacements, $this->_dest_source_res . "/values/strings.xml");
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
        $keystorePath = path(self::BACKWARD_ANDROID . '/keystore/' . $keystoreFilename);
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
        $releaseSigning = path("{$this->_dest_source}/release-signing.properties");
        $signing = "keyAlias={$alias}
keyPassword={$keypass}
storeFile=keystore.pks
storePassword={$storepass}";
        File::putContents($releaseSigning, $signing);

        return $keystore;
    }

}
