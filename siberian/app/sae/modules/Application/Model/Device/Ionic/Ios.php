<?php

use Siberian\File;

/**
 * Class Application_Model_Device_Ionic_Ios
 */
class Application_Model_Device_Ionic_Ios extends Application_Model_Device_Ionic_Ios_Abstract
{
    /**
     * @var string
     */
    const SOURCE_FOLDER = "/var/apps/ionic/ios";

    /**
     * @var string
     */
    const DEST_FOLDER = "/var/tmp/applications/ionic/ios/%s/AppsMobileCompany";

    /**
     * @var string
     */
    const ARCHIVE_FOLDER = "/var/tmp/applications/ionic";

    /**
     * @var string
     */
    protected $_current_version = '2.0';

    /**
     * @var string
     */
    protected $_zipname;

    /**
     * @var string
     */
    protected $_new_xml;

    /**
     * @var
     */
    protected $_request;

    /**
     * @var string
     */
    protected $_orig_source;

    /**
     * @var string
     */
    protected $_orig_source_amc;

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
    protected $_dest_source_amc;

    /**
     * @var string
     */
    protected $_dest_source_res;

    /**
     * @var string
     */
    protected $_dest_archive;

    /**
     * @var bool
     */
    public $withAds = false;

    /**
     * @var string
     */
    public $admobAppIdentifier = 'ca-app-pub-0000000000000000~0000000000';

    /**
     * Application_Model_Device_Ionic_Ios constructor.
     * @param array $data
     * @throws Zend_Exception
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->_os_name = 'ios';
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
     * @var array
     */
    public static $_store_categories = [
        1 => "Business",
        2 => "Catalogs",
        3 => "Education",
        4 => "Entertainment",
        5 => "Finance",
        6 => "Food & Drink",
        7 => "Games",
        8 => "Health & Fitness",
        9 => "Lifestyle",
        10 => "Medical",
        11 => "Audio",
        12 => "Navigation",
        13 => "News",
        14 => "Photo & Video",
        15 => "Productivity",
        16 => "Reference",
        17 => "Social Networking",
        18 => "Sports",
        19 => "Travel",
        20 => "Utilities",
        21 => "Weather",
        22 => "Book"
    ];

    /**
     * @return array
     */
    public static function getStoreCategeories()
    {
        $categories = [];
        foreach (self::$_store_categories as $key => $category) {
            $category_name = __($category);

            $categories[$category_name] = new Core_Model_Default([
                'id' => $key,
                'name' => __($category_name)
            ]);
        }

        ksort($categories);

        return $categories;
    }

    /**
     * @param $cat_id
     * @return Core_Model_Default|mixed
     */
    public static function getStoreCategory($cat_id)
    {
        foreach (self::getStoreCategeories() as $category) {
            if ($category->getId() == $cat_id) return $category;
        }

        return new Core_Model_Default();
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
        return "App Store";
    }

    /**
     * @return string
     */
    public function getBrandName()
    {
        return "Apple";
    }

    /**
     * @param bool $isApkService
     * @return mixed|string
     * @throws Exception
     * @throws Zend_Controller_Request_Exception
     */
    public function prepareResources($isApkService = false)
    {
        $this->currentApplication = $this->getApplication();

        $this->_package_name = $this->currentApplication->getBundleId();
        $this->_application_id = Core_Model_Lib_String::format($this->currentApplication->getName() . "_" .
            $this->currentApplication->getId(), true);
        $this->_application_name = $this->currentApplication->getName();

        // Prepping paths!
        $this->_preparePathsVars();
        $this->_prepareRequest();
        $this->_cpFolder();
        $this->_cleanAssets();

        $this->replaceBundleId();

        $this->_prepareUrl();
        $this->_prepareLanguages();

        // Shared method!
        $this->buildPList();
        $this->ionicResources($this->currentApplication);

        return $this->zipFolder();
    }

    /**
     *
     */
    protected function _preparePathsVars()
    {
        // Ads!
        $_package_ads_suffix = $_source_ads_suffix = "";
        $this->withAds = true;
        $device = $this->getDevice();
        if ($device->getExcludeAds()) {
            $this->withAds = false;
            $_package_ads_suffix = $_source_ads_suffix = "-noads";
        }
        $this->admobAppIdentifier = trim($device->getAdmobAppId());

        $this->_app_name_formatted = Core_Model_Lib_String::format($this->currentApplication->getName(), true);
        $this->_folder_name = $this->_app_name_formatted . '-' . $this->currentApplication->getId();

        // Ionic sources!
        $this->_orig_source = path(self::SOURCE_FOLDER);
        if (!$this->withAds) {
            $this->_orig_source = str_replace('/ios', '/ios-noads', $this->_orig_source);
        }
        $this->_orig_source_amc = $this->_orig_source . "/AppsMobileCompany";
        $this->_orig_source_res = $this->_orig_source_amc . "/Resources";

        /** /var/tmp/applications/[DESIGN]/[PLATFORM]/[APP_NAME]/AppsMobileCompany */
        $this->_dest_source = path(self::DEST_FOLDER);
        if (!$this->withAds) {
            $this->_dest_source = str_replace('/ios', '/ios-noads', $this->_dest_source);
        }
        $this->_dest_source = sprintf($this->_dest_source, $this->_folder_name);
        $this->_dest_source_amc = $this->_dest_source . "/AppsMobileCompany";
        $this->_dest_source_res = $this->_dest_source_amc . "/";

        $this->_dest_archive = path(self::ARCHIVE_FOLDER);

        /** Vars */
        $this->_zipname = sprintf("%s_%s_%s%s",
            $this->_app_name_formatted, $this->currentApplication->getId(), "ios_source", $_package_ads_suffix);

        if (!$this->_app_name_formatted) {
            $this->_zipname = sprintf("%s_%s_%s%s",
                $this->getDevice()->getAlias(), $this->currentApplication->getId(), "ios_source", $_package_ads_suffix);
        }
    }

    /**
     * url.js is custom for each app!
     */
    protected function _prepareUrl()
    {
        $protocol = "https://";
        if (defined("CRON")) {
            $domain = $this->getDevice()->getHost();
        } else {
            $domain = $this->_request->getHttpHost();
        }

        $app_key = $this->currentApplication->getKey();
        $languages = array_keys(Core_Model_Language::getLanguages());
        $version = \Siberian\Version::VERSION;

        $url_js_content = "
/** Auto-generated url.js */
var REDIRECT_URI = false;
var IS_NATIVE_APP = true;
var DEVICE_TYPE = 2;
var XS_VERSION = '{$version}';
window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, \"\");
var AVAILABLE_LANGUAGES = ['" . implode_polyfill("','", $languages) . "'];
var DISABLE_BATTERY_OPTIMIZATION = false;

// WebView
if (typeof IS_PREVIEW === 'undefined' ||
    (typeof IS_PREVIEW !== 'undefined' && IS_PREVIEW !== true)) {
    PROTOCOL = '{$protocol}';
    DOMAIN = '{$protocol}{$domain}';
    APP_KEY = '{$app_key}';
    BASE_PATH = '/'+APP_KEY;
}

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + '/';";


        File::putContents($this->_dest_source . "/www/js/utils/url.js", $url_js_content);
    }

    /**
     * languages.js is custom for each app!
     */
    protected function _prepareLanguages()
    {
        // deprecated
    }
}
