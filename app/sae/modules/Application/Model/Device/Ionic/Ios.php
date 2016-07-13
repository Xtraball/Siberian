<?php

class Application_Model_Device_Ionic_Ios extends Application_Model_Device_Ionic_Ios_Abstract {

    const SOURCE_FOLDER = "/var/apps/ionic/ios";
    const DEST_FOLDER = "/var/tmp/applications/ionic/ios/%s/AppsMobileCompany";

    protected $_current_version = '1.0';
    protected $_zipname;
    protected $_new_xml;
    protected $_request;
    /** Folders */
    protected $_orig_source;
    protected $_orig_source_amc;
    protected $_orig_source_res;
    protected $_dest_source;
    protected $_dest_source_amc;
    protected $_dest_source_res;

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_os_name = "ios";
        $this->_logger = Zend_Registry::get("logger");
        return $this;
    }

    public static $_store_categories = array(
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
    );

    public static function getStoreCategeories() {
        $categories = array();
        foreach(self::$_store_categories as $key => $category) {
            $category_name = parent::_($category);

            $categories[$category_name] = new Core_Model_Default(array(
                'id' => $key,
                'name' => parent::_($category_name),
            ));
        }

        ksort($categories);

        return $categories;
    }

    public static function getStoreCategory($cat_id) {

        foreach(self::getStoreCategeories() as $category) {
            if($category->getId() == $cat_id) return $category;
        }

        return new Core_Model_Default();
    }

    public function getCurrentVersion() {
        return $this->_current_version;
    }

    public function getStoreName() {
        return "App Store";
    }

    public function getBrandName() {
        return "Apple";
    }


    public function configureAutoupdater($host) {

        $suffixes = array("","-noads");

        foreach ($suffixes as $suffix) {
            $orig_source = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER.$suffix);

            //Set correct url to config.xml
            $configXMLPath = "{$orig_source}/AppsMobileCompany/config.xml";
            $this->__replace(
                array(
                    '~(<config-file url=").*(" />)~i' => '$1'.$host.self::SOURCE_FOLDER.$suffix.'/www/chcp.json$2',
                ),
                $configXMLPath,
                true
            );

            //Update configuration file
            $chcpConfigPath = "{$orig_source}/www/chcp.json";
            $chcpConfig = array(
                "content_url" => $host.self::SOURCE_FOLDER.$suffix.'/www',
                "min_native_interface" => Siberian_Version::NATIVE_VERSION,
                "release" => Siberian_Version::VERSION
            );

            if(!file_put_contents($chcpConfigPath,Zend_Json::encode($chcpConfig))) {
                throw new Exception("Cannot write to file " . $chcpConfigPath);
            }
        }
    }


    public function prepareResources() {

        $this->_application = $this->getApplication();

        $this->_package_name = $this->_application->getBundleId();
        $this->_application_id = Core_Model_Lib_String::format($this->_application->getName()."_".$this->_application->getId(), true);
        $this->_application_name = $this->_application->getName();

        /** Prepping paths */
        $this->_preparePathsVars();
        $this->_prepareRequest();
        $this->_cpFolder();
        $this->_prepareUrl();
        $this->_prepareLanguages();
        $this->_admob();

        /** Shared method */
        $this->buildPList();
        $this->ionicResources($this->_application);

        $zip = $this->zipFolder();

        return $zip;
    }

    protected function _preparePathsVars() {
        /** Ads */
        $_package_ads_suffix = $_source_ads_suffix = "";
        $_dest_ads_suffix = "";
        if($this->getDevice()->getExcludeAds()) {
            $_package_ads_suffix = $_source_ads_suffix = "-noads";
            $_dest_ads_suffix = "NoAds";
        }

        $this->_app_name_formatted = Core_Model_Lib_String::format($this->_application->getName(), true);
        $this->_folder_name = $this->_app_name_formatted.'-'.$this->_application->getId();

        /** Ionic sources */
        $this->_orig_source = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER.$_source_ads_suffix);
        $this->_orig_source_amc = $this->_orig_source."/AppsMobileCompany";
        $this->_orig_source_res = $this->_orig_source_amc."/Resources";

        /** /var/tmp/applications/[DESIGN]/[PLATFORM]/[APP_NAME]/AppsMobileCompany */
        $this->_dest_source = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER.$_dest_ads_suffix);
        $this->_dest_source = sprintf($this->_dest_source, $this->_folder_name);
        $this->_dest_source_amc = $this->_dest_source."/AppsMobileCompany";
        $this->_dest_source_res = $this->_dest_source_amc."/Resources";

        /** Vars */
        $this->_zipname = $this->_app_name_formatted.'_ios_source'.$_package_ads_suffix;

        if(!$this->_app_name_formatted) {
            $this->_app_name_formatted = $this->_application->getId();
            $this->_zipname = $this->getDevice()->getAlias().'_ios_source'.$_package_ads_suffix;
        }
    }


    /** App only */
    protected function _prepareUrl() {

        $domain = $this->_request->getHttpHost();
        $app_key = $this->_application->getKey();

        $url_js_content = "
/** Auto-generated url.js */
var REDIRECT_URI = false;
window.location.hash = window.location.hash.replace(/\?__goto__=(.*)/, \"\");
var CURRENT_LANGUAGE = AVAILABLE_LANGUAGES.indexOf(language) >= 0 ? language : 'en';
DOMAIN = 'http://{$domain}';
APP_KEY = '{$app_key}';
BASE_PATH = '/'+APP_KEY;

var BASE_URL = DOMAIN + BASE_PATH;
var IMAGE_URL = DOMAIN + '/';";

        file_put_contents($this->_dest_source."/www/js/utils/url.js", $url_js_content);

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

        file_put_contents($this->_dest_source."/www/js/utils/languages.js", $file_content);

    }

    private function __getUrlValue($key) {

        $value = null;

        switch($key) {
            case "url_scheme": $value = $this->_request->getScheme(); break;
            case "url_domain": $value = $this->_request->getHttpHost(); break;
            case "url_path": $value = ltrim($this->_request->getBaseUrl(), "/"); break;
            case "url_key":
                if($this->_request->useApplicationKey()) {
                    $value = $this->_application->getKey();
                }
                break;
            default: $value = "";
        }

        return $value;
    }

}
