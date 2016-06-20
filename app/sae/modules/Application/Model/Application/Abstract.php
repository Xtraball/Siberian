<?php

abstract class Application_Model_Application_Abstract extends Core_Model_Default {

    const PATH_IMAGE = '/images/application';
    const PATH_TEMPLATES = '/images/templates';
    const OVERVIEW_PATH = 'overview';
    const BO_DISPLAYED_PER_PAGE = 1000;
    const PATH_TO_SOURCE_CODE = "/var/apps/browser/index.html#/";
    const DESIGN_CODE_ANGULAR = "angular";
    const DESIGN_CODE_IONIC = "ionic";

    protected $_startup_image;
    protected $_customers;
    protected $_options;
    protected $_pages;
    protected $_uses_user_account;
    protected $_layout;
    protected $_devices;
    protected $_design;
    protected $_design_blocks;
    protected $_admin_ids = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Application';
    }

    public function findByHost($host, $path = null) {

        if(!empty($path)) {
            $uri = explode('/', ltrim($path, '/'));
            $i = 0;
            while($i <= 1) {
                if(!empty($uri[$i])) {
                    $value = $uri[$i];
                    $this->find($value, 'tmp_key');
                    if($this->getId()) {
                        $this->setUseTmpKey('1');
                        break;
                    }
                }
                $i++;
            }
        }

        if(!$this->getId()) {

            if(!in_array($host[0], array('www'))) {
                $this->find($host, 'domain');
            }
        }

        return $this;

    }
    
    public function findAllByAdmin($admin_id) {
        return $this->getTable()->findAllByAdmin($admin_id);
    }

    public function findAllToPublish() {
        return $this->getTable()->findAllToPublish();
    }

    public function getOwner() {

        $admin = new Admin_Model_Admin();
        $admin->find($this->getAdminId());
        return $admin;

    }

    public function save() {

        if(!$this->getId()) {
            $this->setKey(uniqid())
                ->setDesignCode(self::DESIGN_CODE_IONIC)
            ;
            if(!$this->getLayoutId()) {
                $this->setLayoutId(1);
            }
        }

        parent::save();

        if(!empty($this->_admin_ids)) {
            foreach($this->_admin_ids as $admin_id) {
                $this->getTable()->addAdmin($this->getId(), $admin_id);
            }
        }

        return $this;

    }

    public function addAdmin($admin) {

        if($this->getId()) {
            $is_allowed_to_add_pages = $admin->hasIsAllowedToAddPages() ? $admin->getIsAllowedToAddPages() : true;
            $this->getTable()->addAdmin($this->getId(), $admin->getId(), $is_allowed_to_add_pages);
        } else {
            if (!in_array($admin->getId(), $this->_admin_ids)) {
                $this->_admin_ids[] = $admin->getId();
            }
        }

        return $this;

    }

    public function removeAdmin($admin) {
        $this->getTable()->removeAdmin($this->getId(), $admin->getId());
        return $this;
    }

    public function setAdminIds($adminIds) {
        $this->_admin_ids = $adminIds;
        return $this;
    }

    public function getAdmins() {
        return $this->getTable()->getAdminIds($this->getId());
    }

    public function hasAsAdmin($admin_id) {
        return (bool) $this->getTable()->hasAsAdmin($this->getId(), $admin_id);
    }

    public function getDevices() {

        $device_ids = array_keys(Application_Model_Device::getAllIds());
        foreach($device_ids as $device_id) {
            if(empty($this->_devices[$device_id])) {
                $this->getDevice($device_id);
            }
        }

        return $this->_devices;

    }

    public function getDevice($device_id) {

        if(empty($this->_devices[$device_id])) {
            $device = new Application_Model_Device();
            $device->find(array("app_id" => $this->getId(), "type_id" => $device_id));
            if(!$device->getId()) {
                $device->loadDefault($device_id);
                $device->setAppId($this->getId());
            }
            $device->setDesignCode($this->getDesignCode());
            $this->_devices[$device_id] = $device;
        }

        return $this->_devices[$device_id];

    }

    public function useIonicDesign() {
        return $this->getDesignCode() == self::DESIGN_CODE_IONIC;
    }

    public function getDesign() {

        if(!$this->_design) {
            $this->_design = new Template_Model_Design();
            if($this->getDesignId()) {
                $this->_design->find($this->getDesignId());
            }
        }

        return $this->_design;

    }

    public function setDesign($design, $category = null) {

        $image_name = uniqid().'.png';
        $relative_path = '/homepage_image/bg/';
        $lowres_relative_path = '/homepage_image/bg_lowres/';

        if(!is_dir(self::getBaseImagePath().$lowres_relative_path)) {
            mkdir(self::getBaseImagePath().$lowres_relative_path, 0777, true);
        }

        if(!copy($design->getBackgroundImage(true), self::getBaseImagePath().$lowres_relative_path.$image_name)) {
            throw new Exception($this->_('#101: An error occurred while saving'));
        }

        if(!is_dir(self::getBaseImagePath().$relative_path)) {
            mkdir(self::getBaseImagePath().$relative_path, 0777, true);
        }
        if(!copy($design->getBackgroundImageHd(true), self::getBaseImagePath().$relative_path.$image_name)) {
            throw new Exception($this->_('#102: An error occurred while saving'));
        }

        foreach($design->getBlocks() as $block) {
            $block->setAppId($this->getId())->save();
        }

        $this->setDesignId($design->getId())
            ->setLayoutId($design->getLayoutId())
            ->setLayoutVisibility($design->getLayoutVisibility())
            ->setOverView($design->getOverview())
            ->setBackgroundImage($design->getBackgroundImage())
            ->setBackgroundImageHd($design->getBackgroundImageHd())
            ->setBackgroundImageTablet($design->getBackgroundImageTablet())
            ->setIcon($design->getIcon())
            ->setStartupImage($design->getStartupImage())
            ->setStartupImageRetina($design->getStartupImageRetina())
            ->setStartupImageIphone6($design->getStartupImageIphone6())
            ->setStartupImageIphone6Plus($design->getStartupImageIphone6Plus())
            ->setStartupImageIpadRetina($design->getStartupImageIpadRetina())
            ->setHomepageBackgroundImageRetinaLink($relative_path.$image_name)
            ->setHomepageBackgroundImageLink($lowres_relative_path.$image_name)
        ;

        if(!$this->getOptionIds() AND $category->getId()) {
            $this->createDummyContents($category, null, $category);
        }

        return $this;

    }

    public function createDummyContents($category, $design = null, $category = null) {

        $design = is_null($design) ? $this->getDesign() : $design;
        $design_content = new Template_Model_Design_Content();
        $design_contents = $design_content->findAll(array('design_id' => $design->getDesignId()));

        foreach($design_contents as $content) {

            $option_value = new Application_Model_Option_Value();
            $option = new Application_Model_Option();
            $option->find($content->getOptionId());

            if(!$option->getId()) continue;

            $option_value->setOptionId($content->getOptionId())
                ->setAppId($this->getApplication()->getId())
                ->setTabbarName($content->getOptionTabbarName())
                ->setIconId($content->getOptionIcon())
                ->setBackgroundImage($content->getOptionBackgroundImage())
                ->save()
            ;

            if($option->getModel() && $option->getCode() != "push_notification") {
                $option->getObject()->createDummyContents($option_value, $design, $category);
            }
        }
    }

    public function getBlocks($type_id = null) {

        if(!$type_id) {
            $type_id = $this->useIonicDesign() ? Template_Model_Block::TYPE_IONIC_APP : Template_Model_Block::TYPE_APP;
        }

        $block = new Template_Model_Block();
        if(empty($this->_design_blocks)) {
            $this->_design_blocks = $block->findAll(array('app_id' => $this->getId(), 'type_id' => $type_id), 'position ASC');

            if(!empty($this->_design_blocks)) {
                foreach($this->_design_blocks as $block) {
                    $block->setApplication($this);
                }
            }
        }

        return $this->_design_blocks;
    }

    public function getBlock($code) {

        if($this->useIonicDesign() AND $code == "tabbar") {
            $code = "homepage";
        }
        $blocks = $this->getBlocks();

        foreach($blocks as $block) {
            if($block->getCode() == $code) return $block;
            else if($block->getChildren()) {
                foreach($block->getChildren() as $child) {
                    if($child->getCode() == $code) return $child;
                }
            }
        }

        return new Template_Model_Block();
    }

    public function setBlocks($blocks) {
        $this->_design_blocks = $blocks;
        return $this;
    }

    public function getLayout() {

        if(!$this->_layout) {
            $this->_layout = new Application_Model_Layout_Homepage();
            $this->_layout->find($this->getLayoutId());
        }

        return $this->_layout;

    }

    public function getBundleId() {

        $bundle_id = $this->getData("bundle_id");

        $bundle_id_parts = explode(".", $bundle_id);

        if(count($bundle_id_parts) != count(array_filter($bundle_id_parts))) {

            $url = Zend_Uri::factory(parent::getUrl(""))->getHost();
            $url = array_reverse(explode(".", $url));
            $url[] = "app".$this->getKey();

            $bundle_id = Core_Model_Lib_String::formatBundleId($url);
        } else {
            $bundle_id = Core_Model_Lib_String::formatBundleId($bundle_id_parts);
        }

        if($bundle_id != $this->getData("bundle_id")) {
            $this->setBundleId($bundle_id)->save();
        }

        return $bundle_id;
    }

    public function isActive() {
        return (bool) $this->getData("is_active");
    }

    public function isLocked() {
        return (bool) $this->getData("is_locked");
    }

    public function canBePublished() {
        return (bool) $this->getData("can_be_published");
    }

    public function isSomeoneElseEditingIt() {
        return $this->getTable()->isSomeoneElseEditingIt($this->getId(), Zend_Session::getId());
    }

    public function getCustomers() {

        if(is_null($this->_customers)) {
            $customer = new Customer_Model_Customer();
            $this->_customers = $customer->findAll(array("app_id" => $this->getId()));
        }

        return $this->_customers;

    }

    public function getOptions() {

        if(empty($this->_options)) {
            $option = new Application_Model_Option_Value();
            $this->_options = $option->findAll(array("a.app_id" => $this->getId(), "is_visible" => 1));
        }

        return $this->_options;

    }

    public function getOptionIds() {

        $option_ids = array();
        $options = $this->getOptions();
        foreach($options as $option) {
            $option_ids[] = $option->getOptionId();
        }

        return $option_ids;

    }

    public function getOption($code) {

        $option_sought = new Application_Model_Option();
        $dummy = new Application_Model_Option();
        $dummy->find($code, 'code');
        foreach($this->getOptions() as $page) {
            if($page->getOptionId() == $dummy->getId()) $option_sought = $page;
        }

        return $option_sought;

    }

    public function getPages($samples = 0) {

        if(empty($this->_pages)) {
            $option = new Application_Model_Option_Value();
            $this->_pages = $option->findAll(array("a.app_id" => $this->getId(), 'remove_folder' => new Zend_Db_Expr('folder_category_id IS NULL'), 'is_visible' => 1/*, '`aov`.`is_active`' => 1*/));
        }
        if($this->_pages->count() == 0 AND $samples > 0) {
            $dummy = Application_Model_Option_Value::getDummy();
            for($i = 0; $i < $samples; $i++) {
                $this->_pages->addRow($this->_pages->count(), $dummy);
            }
        }

        return $this->_pages;

    }

    public function getPage($code) {

        $dummy = new Application_Model_Option();
        $dummy->find($code, 'code');

        $page_sought = new Application_Model_Option_Value();
        return $page_sought->find(array('app_id' => $this->getId(), 'option_id' => $dummy->getId()));

    }

    public function getFirstActivePage() {
        foreach($this->getPages() as $page) {
            if($page->isActive()) {
                if($page->getCode() != "padlock" AND (!$page->isLocked() OR $this->getSession()->getCustomer()->canAccessLockedFeatures())) {
                    return $page;
                }
            }
        }
        return new Application_Model_Option_Value();
    }

    public function getTabbarAccountName() {
        if($this->hasTabbarAccountName()) return $this->getData('tabbar_account_name');
        else return $this->_('My account');
    }

    public function getShortTabbarAccountName() {
        return Core_Model_Lib_String::formatShortName($this->getTabbarAccountName());
    }

    public function getTabbarMoreName() {
        if($this->hasTabbarMoreName()) return $this->getData('tabbar_more_name');
        else return $this->_('More');
    }

    public function getShortTabbarMoreName() {
        return Core_Model_Lib_String::formatShortName($this->getTabbarMoreName());
    }

    public function usesUserAccount() {

        if(is_null($this->_uses_user_account)) {
            $this->_uses_user_account = false;
            $codes = array('newswall', 'fanwall', 'padlock', 'discount', 'loyalty');
            foreach($codes as $code) {
                $option = $this->getOption($code);
                if($option->getId() AND $option->isActive()) $this->_uses_user_account = true;
            }
        }

        return $this->_uses_user_account;
    }

    public function getCountryCode() {
        $code = $this->getData('country_code');
        if(is_null($code)) {
            $code = Core_Model_Language::getCurrentLocaleCode();
        }
        return $code;
    }

    public function isPublished() {

        foreach($this->getDevices() as $device) {
            if($device->isPublished()) return true;
        }

        return false;

    }

    public function getQrcode($uri = null, $params = array()) {
        $qrcode = new Core_Model_Lib_Qrcode();
        $url = "";
        if(filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $url = $uri;
        } else {
            //$url = $this->getUrl($uri);
            $url = $this->getBaseUrl() . $this->getPath("application/device/check", array("app_id" => $this->getAppId()));
        }

        return $qrcode->getImage($this->getName(), $url, $params);
    }

    public static function getImagePath() {
        return Core_Model_Directory::getPathTo(static::PATH_IMAGE);
    }
    public static function getBaseImagePath() {
        return Core_Model_Directory::getBasePathTo(static::PATH_IMAGE);
    }

    public static function getTemplatePath() {
        return Core_Model_Directory::getPathTo(self::PATH_TEMPLATES);
    }
    public static function getBaseTemplatePath() {
        return Core_Model_Directory::getBasePathTo(self::PATH_TEMPLATES);
    }
    public static function getDesignCodes() {
        return array(
            self::DESIGN_CODE_ANGULAR => ucfirst(self::DESIGN_CODE_ANGULAR),
            self::DESIGN_CODE_IONIC => ucfirst(self::DESIGN_CODE_IONIC)
        );
    }

    public function getLogo() {
        $logo = self::getImagePath().$this->getData('logo');
        $base_logo = self::getBaseImagePath().$this->getData('logo');
        if(is_file($base_logo) AND file_exists($base_logo)) return $logo;
        else return self::getImagePath().'/placeholder/no-image.png';
    }

    public function getIcon($size = null, $name = null, $base = false) {

        if(!$size) $size = 114;

        $icon = self::getBaseImagePath().$this->getData('icon');
        if(!is_file($icon) OR !file_exists($icon)) $icon = self::getBaseImagePath().'/placeholder/no-image.png';

        if(empty($name)) $name = sha1($icon.$size);
        $name .= '_'.filesize($icon);

        $newIcon = new Core_Model_Lib_Image();
        $newIcon->setId($name)
            ->setPath($icon)
            ->setWidth($size)
            ->crop()
        ;
        return $newIcon->getUrl($base);
    }

    public function getIconUrl($size = null) {
        $icon = $this->getIcon($size);
        if(substr($icon,0,1) == "/") $icon = substr($icon,1,strlen($icon)-1);
        return Core_Model_Url::create().$icon;
    }

    public function getAllPictos() {
        $picto_urls = array();
        foreach($this->getBlocks() as $block) {
            $dir = Core_Model_Directory::getDesignPath(true, "/images/pictos/", "mobile");
            $pictos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($pictos as $picto) {
                $colorized_color = Core_Model_Lib_Image::getColorizedUrl($picto->getPathName(), $block->getColor());
                $colorized_background_color = Core_Model_Lib_Image::getColorizedUrl($picto->getPathName(), $block->getBackgroundColor());
                $picto_urls[$colorized_color] = $colorized_color;
                $picto_urls[$colorized_background_color] = $colorized_background_color;
            }
        }

        return $picto_urls;
    }

    public function getAppStoreIcon($base = false) {
        return $this->getIcon(1024, 'touch_icon_'.$this->getId(). '_1024', $base);
    }

    public function getGooglePlayIcon($base = false) {
        return $this->getIcon(512, 'touch_icon_'.$this->getId(). '_512', $base);
    }

    public function getStartupImageUrl($type = "standard", $base = false) {

        try {
            $image = '';

            if($type == "standard") $image_name = $this->getData('startup_image');
            else $image_name = $this->getData('startup_image_'.$type);

            if(!empty($image_name) AND file_exists(self::getBaseImagePath().$image_name)) {
                $image = $base ? self::getBaseImagePath().$image_name : self::getImagePath().$image_name;
            }

        }
        catch(Exception $e) {
            $image = '';
        }

        if(empty($image)) {
            $image = $this->getNoStartupImageUrl($type, $base);
        }

        return $image;
    }

    public function getNoStartupImageUrl($type = 'standard', $base = false) {

        if($type == "standard") $type = "";
        else $type = "-".str_replace("_", "-", $type);

        $image_name = "no-startupimage{$type}.png";

        $path = $base ? self::getBaseImagePath() : self::getImagePath();
        return $path."/placeholder/".$image_name;
    }

    public function getShortName() {

        if($name = $this->getName()) {
            if(mb_strlen($name, 'UTF-8') > 11) $name = trim(mb_substr($name, 0, 10, "UTF-8")) . '...';
        }

        return $name;

    }

    public function getFacebookId() {

        $facebook_app_id = $this->getData("facebook_id");

        if(!$facebook_app_id) {
            $facebook_app_id = Api_Model_Key::findKeysFor('facebook')->getAppId();
        }

        return $facebook_app_id;
    }

    public function getFacebookKey() {

        $facebook_key = $this->getData("facebook_key");

        if(!$facebook_key) {
            $facebook_key = Api_Model_Key::findKeysFor('facebook')->getSecretKey();
        }

        return $facebook_key;
    }

    public function getInstagramClientId() {

        $instagram_client_id = $this->getData("instagram_client_id");

        if(!$instagram_client_id) {
            $instagram_client_id = Api_Model_Key::findKeysFor('instagram')->getClientId();
        }

        return $instagram_client_id;
    }

    public function getInstagramToken() {

        $instagram_token = $this->getData("instagram_token");

        if(!$instagram_token) {
            $instagram_token = Api_Model_Key::findKeysFor('instagram')->getToken();
        }

        return $instagram_token;
    }

    public function updateOptionValuesPosition($positions) {
        $this->getTable()->updateOptionValuesPosition($positions);
        return $this;
    }

    public function subscriptionIsActive() {
        if(Siberian_Version::TYPE != "PE") return true;
        return $this->getSubscription()->isActive();
    }

    public function isAvailableForPublishing($check_sources_access_type) {
        $errors = array();
        if($this->getPages()->count() < 3) $errors[] = $this->_("At least, 4 pages in the application");
        if(!$this->getData('background_image')) $errors[] = $this->_("The homepage image");
        if(!$this->getStartupImage()) $errors[] = $this->_("The startup image");
        if(!$this->getData('icon')) $errors[] = $this->_("The desktop icon");
        if(!$this->getName()) $errors[] = $this->_("The application name");
        if($check_sources_access_type) {
            if(!$this->getBundleId()) $errors[] = $this->_("The bundle id");
        } else {
            if(!$this->getDescription()) $errors[] = $this->_("The description");
            else if(strlen($this->getDescription()) < 200) $errors[] = $this->_("At least 200 characters in the description");
            if(!$this->getKeywords()) $errors[] = $this->_("The keywords");
            if(!$this->getMainCategoryId()) $errors[] = $this->_("The main category");
        }

        return $errors;
    }

    public function isFreeTrialExpired() {
        if(Siberian_Version::TYPE != "PE") return false;

        $date_expire_at = $this->getFreeUntil();
        if(!$date_expire_at) return false;

        $date = new Zend_Date();
        $date_until = new Zend_Date($date_expire_at, "y-MM-d HH:mm:ss");

        $diff = $date->compare($date_until);
        return $diff > 0;
    }

    public function getBackgroundImageUrl($type = 'normal') {

        try {
            $backgroundImage = '';
            if($background_image = $this->getData('background_image')) {
                if($type == 'normal') $background_image .= '.jpg';
                else if($type == 'retina') $background_image .= '@2x.jpg';
                else if($type == 'retina4') $background_image .= '-568h@2x.jpg';
                if(file_exists(self::getBaseImagePath().$background_image)) {
                    $backgroundImage = self::getImagePath().$background_image;
                }
            }
        }
        catch(Exception $e) {
            $backgroundImage = '';
        }

        if(empty($backgroundImage)) {
            $backgroundImage = $this->getNoBackgroundImageUrl($type);
        }

        return $backgroundImage;
    }

    public function getHomepageBackgroundImageUrl($type = '') {

        try {

            $image = '';

            switch($type) {
                case "hd": $image_name = $this->getData('background_image_hd'); break;
                case "tablet": $image_name = $this->getData('background_image_tablet'); break;
                case "standard":
                default: $image_name = $this->getData('background_image'); break;
            }

            if(!empty($image_name)) {
                if(file_exists(self::getBaseImagePath().$image_name)) {
                    $image = self::getImagePath() . $image_name;
                } else if(file_exists(self::getBaseTemplatePath().$image_name)) {
                    $image = self::getTemplatePath() . $image_name;
                }
            }

        }
        catch(Exception $e) {
            $image = '';
        }

        if(empty($image)) {
            $image = $this->getNoBackgroundImageUrl($type);
        }

        return $image;
    }

    public function getSliderImages() {

        try {

            $library = new Media_Model_Library();
            $images = $library->find($this->getApplication()->getHomepageSliderLibraryId())->getImages();

        } catch(Exception $e) {
            $images = array();
        }

        return $images;
    }

    public function getNoBackgroundImageUrl($type = 'standard') {

        switch($type) {
            case "hd": $image_name = "no-background-hd.jpg"; break;
            case "tablet": $image_name = "no-background-tablet.jpg"; break;
            case "standard":
            default: $image_name = "no-background.jpg"; break;
        }

        return self::getImagePath()."/placeholder/$image_name";
    }

    public function getUrl($url = '', array $params = array(), $locale = null, $forceKey = false) {

        $is_ionic_url = false;
        if(!empty($params["use_ionic"])) {
            $is_ionic_url = true;
            unset($params["use_ionic"]);
        }

        if(!$this->getDomain()) $forceKey = true;

        if($is_ionic_url) {
            $url = Core_Model_Url::create($url, $params, $locale);
        } else if($forceKey) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $use_key = $request->useApplicationKey();
            $request->useApplicationKey(true);
            $url = Core_Model_Url::create($url, $params, $locale);
            $request->useApplicationKey($use_key);
        } else {
            $domain = rtrim($this->getDomain(), "/")."/";
            $url = Core_Model_Url::createCustom('http://'.$domain, $url, $params, $locale);
        }

        if(substr($url, strlen($url) -1, 1) != "/") {
            $url .= "/";
        }

        return $url;

    }

    public function getIonicUrl($url = '', array $params = array(), $locale = null, $forceKey = false) {

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $params["use_ionic"] = true;

        $oldKey = $request->useApplicationKey();
        $ionic_path = $request->getIonicPath();

        $request->useApplicationKey(true);
        $request->setIonicPath(self::getIonicPath());

        $url = $this->getUrl($url, $params, $locale, $forceKey);

        $request->useApplicationKey($oldKey);
        $request->setIonicPath($ionic_path);

        return $url;
    }

    public function getPath($uri = '', array $params = array(), $locale = null) {

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $useKey = (bool) $request->useApplicationKey();
        $request->useApplicationKey(true);
        if($this->getValueId()) {
            $param["value_id"] = $this->getValueId();
        }
        $url = parent::getPath($uri, $params, $locale);
        $request->useApplicationKey($useKey);

        return $url;

    }

    public static function getIonicPath() {
        return trim(Core_Model_Directory::getPathTo(self::PATH_TO_SOURCE_CODE), "/");
    }

    public function requireToBeLoggedIn() {
        return $this->getData('require_to_be_logged_in');
    }

    public function duplicate() {

        // Retrieve all the accounts
        $admins = $this->getAdmins();
        $admin_ids = array();
        foreach($admins as $admin) {
            $admin_ids[] = $admin;
        }

        // Retrieve the design
        $blocks = array();
        foreach($this->getBlocks() as $block) {
            $blocks[] = $block->getData();
        }
        $layout_id = $this->getLayoutId();

        // Load the options
        $option_values = $this->getOptions();
        $value_ids = array();

        // Save the new application
        $old_app_id = $this->getId();
        $this->setId(null)
            ->setName($this->getName() . " (Copy)")
            ->setLayoutId($layout_id)
            ->unsCreatedAt()
            ->unsUpdatedAt()
            ->setBundleId(null)
            ->setDomain(null)
            ->setSubdomain(null)
            ->setSubdomainIsValidated(null)
            ->save()
        ;

        // Save the design
        if(!empty($blocks)) {
            foreach($blocks as $template_block) {
                $block = new Template_Model_Block();
                $block->setData($template_block);
                $block->setAppId($this->getId());
                $block->save();
            }
        }
        $this->setLayoutId($layout_id)
            ->save();

        // Copy all the features but folders
        foreach($option_values as $option_value) {
            if($option_value->getCode() != 'folder') {
                $option_value->copyTo($this);
                $value_ids[$option_value->getOldValueId()] = $option_value->getId();
            }
        }

        // Copy the folders
        foreach($option_values as $option_value) {
            if($option_value->getCode() == 'folder') {
                $option_value->copyTo($this);
                $value_ids[$option_value->getOldValueId()] = $option_value->getId();
            }
        }

        // Lock the features
        $locker = new Padlock_Model_Padlock();
        $old_locked_value_ids = $locker->getValueIds($old_app_id);
        $locked_value_ids = array();
        foreach($old_locked_value_ids as $old_locked_value_id) {
            if(!empty($value_ids[$old_locked_value_id])) {
                $locked_value_ids[] = $value_ids[$old_locked_value_id];
            }
        }

        if(!empty($locked_value_ids)) {
            $locker->setValueIds($locked_value_ids)
                ->saveValueIds($this->getId())
            ;
        }

        // Set the accounts to the application
        $this->setAdminIds($admin_ids);
        $this->save();

        //copy slideshow if needed
        if($this->getHomepageSliderIsVisible()) {
            $app_id = $this->getId();
            //create new lib
            $library = new Media_Model_Library();
            $library->setName("homepage_slider_".$app_id)
                ->save();
            $library_id = $library->getId();

            //duplicate current images
            $library_image = new Media_Model_Library_Image();
            $images = $library_image->findAll(
                array("library_id" => $this->getHomepageSliderLibraryId())
            );
            foreach($images as $image) {
                $oldLink = $image->getLink();
                $explodedLink = explode("/", $oldLink);
                $explodedLink[3] = $app_id;

                $newLink = implode("/",$explodedLink);

                //copy file
                mkdir(dirname(getcwd().$newLink), 0777, true);
                copy(getcwd().$oldLink, getcwd().$newLink);

                //duplicate db entry
                $newModelImage = new Media_Model_Library_Image();
                $newModelImage
                    ->setLibraryId($library_id)
                    ->setLink($newLink)
                    ->setAppId($app_id)
                    ->setPosition($image->getPosition())
                    ->save();
            }

            //change library slideshow image
            $this->setHomepageSliderLibraryId($library_id)
                ->save();
        }

        return $this;

    }

    public static $singleton = null;

    public static function setSingleton($application) {
        self::$singleton = $application;
    }

    public static function getSingleton() {
        return self::$singleton;
    }
}
