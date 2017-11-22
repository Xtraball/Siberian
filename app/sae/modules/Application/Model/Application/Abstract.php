<?php

/**
 * Class Application_Model_Application_Abstract
 *
 * @method string getFlickrKey()
 * @method string getFlickrSecret()
 */
abstract class Application_Model_Application_Abstract extends Core_Model_Default {

    const PATH_IMAGE = '/images/application';
    const PATH_TEMPLATES = '/images/templates';
    const OVERVIEW_PATH = 'overview';
    const BO_DISPLAYED_PER_PAGE = 1000;
    const PATH_TO_SOURCE_CODE = "/var/apps/browser/index-prod.html#/";
    const DESIGN_CODE_ANGULAR = "angular";
    const DESIGN_CODE_IONIC = "ionic";

    protected $_startup_image;
    protected $_customers;
    protected $_options;
    protected $_pages;
    protected $_layout;
    protected $_devices;
    protected $_design;
    protected $_design_blocks;
    protected $_admin_ids = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Application';
    }

    /**
     * Testing if a value_id belongs to the current app
     *
     * @todo Allowing cross-app access
     *
     * @param $value_id
     * @return bool
     */
    public function valueIdBelongsTo($value_id) {

        # Handle special cases.
        if(in_array($value_id, array("home"))) {
            return true;
        }

        $option_value_model = new Application_Model_Option_Value();
        $result = $option_value_model->valueIdBelongsTo($value_id, $this->getId());

        # Cross application access.
        /**
         * Check if there is any cross-application access registered, and check for value_id against all the app_ids
         */

        return $result;
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

    public function findAllByAdmin($admin_id, $where = array(), $order = null, $count = null, $offset = null) {
        return $this->getTable()->findAllByAdmin($admin_id, $where, $order, $count, $offset);
    }

    public function findAllToPublish() {
        return $this->getTable()->findAllToPublish();
    }

    public function getOwner() {

        $admin = new Admin_Model_Admin();
        $admin->find($this->getAdminId());
        return $admin;

    }

    /**
     * @return array|mixed|null|string
     */
    public function getPrivacyPolicy() {
        $data = $this->getData("privacy_policy");
        $data = trim(strip_tags($data));
        if(empty($data)) {
            $config_pp = System_Model_Config::getValueFor("privacy_policy");
            $this->setData("privacy_policy", $config_pp)->save();
        }

        return $this->getData("privacy_policy");
    }

    public function save() {

        if(!$this->getId()) {

            // Check if values are valid!
            $applicationName = trim($this->getData('name'));
            if (empty($applicationName)) {
                throw new Siberian_Exception(__('Name is required to save the Application.'));
            }

            // Force trim name on save.
            $this->setName(trim($this->getData('name')));

            if (!Siberian_Version::is('sae')) {
                $adminId = trim($this->getData('admin_id'));
                if (empty($adminId) || ($adminId === 0) || ($adminId === '0')) {
                    throw new Siberian_Exception(__('AdminId is required to save the Application.'));
                }
            }

            $this->setKey(uniqid())
                ->setDesignCode(self::DESIGN_CODE_IONIC)
            ;
            if (!$this->getLayoutId()) {
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

    /**
     * @param $device_id
     * @return Application_Model_Device_Ionic_Android|Application_Model_Device_Ionic_Ios
     */
    public function  getDevice($device_id) {

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
            throw new Exception(__('#101: An error occurred while saving'));
        }

        if(!is_dir(self::getBaseImagePath().$relative_path)) {
            mkdir(self::getBaseImagePath().$relative_path, 0777, true);
        }
        if(!copy($design->getBackgroundImageHd(true), self::getBaseImagePath().$relative_path.$image_name)) {
            throw new Exception(__('#102: An error occurred while saving'));
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
            ->setBackgroundImageLandscape($design->getBackgroundImageLandscape())
            ->setBackgroundImageLandscapeHd($design->getBackgroundImageLandscapeHd())
            ->setBackgroundImageLandscapeTablet($design->getBackgroundImageLandscapeTablet())
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

    public function getRealLayoutVisibility() {
        $layout = $this->getLayout();
        $layout_visibility = $layout->getVisibility();
        $layout_code = $layout->getCode();
        if($layout_code === "layout_9") {
            return "toggle";
        }

        if($layout_visibility === "always") {

        }
    }

    public function createDummyContents($category, $design = null, $_category = null) {

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
                $category = ($_category != null) ? $_category : $category;
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

    /**
     * @param $code
     * @return Template_Model_Block
     */
    public function getBlock($code) {

        if ($this->useIonicDesign() && $code === 'tabbar') {
            $code = 'homepage';
        }
        $blocks = $this->getBlocks();

        foreach ($blocks as $block) {
            if ($block->getCode() === $code) {
                return $block;
            } else if ($block->getChildren()) {
                foreach($block->getChildren() as $child) {
                    if($child->getCode() === $code) {
                        return $child;
                    }
                }
            }
        }

        return (new Template_Model_Block());
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

    /**
     * @param $bundle_id
     * @throws Exception
     */
    public function setBundleId($bundle_id) {
        $regex_ios = "/^([a-z]){2,10}\.([a-z-]{1}[a-z0-9-]*){1,30}((\.([a-z-]{1}[a-z0-9-]*){1,61})*)?$/i";

        if(preg_match($regex_ios, $bundle_id)) {
            $this->setData("bundle_id", $bundle_id)->save();
        } else {
            throw new Exception(__("Your bundle id is invalid, format should looks like com.mydomain.iosid"));
        }

        return $this;
    }

    /**
     * @param $package_name
     * @throws Exception
     */
    public function setPackageName($package_name) {
        $regex_android = "/^([a-z]{1}[a-z_]*){2,10}\.([a-z]{1}[a-z0-9_]*){1,30}((\.([a-z]{1}[a-z0-9_]*){1,61})*)?$/i";

        if(preg_match($regex_android, $package_name)) {
            $this->setData("package_name", $this->validatePackageName($package_name))->save();
        } else {
            throw new Exception(__("Your package name is invalid, format should looks like com.mydomain.androidid"));
        }

        return $this;
    }

    /**
     * get bundle_id, create default if empty
     *
     * @return array|mixed|null|string
     */
    public function getBundleId() {
        $bundle_id = $this->getData("bundle_id");
        if(empty($bundle_id)) {
            $bundle_id = $this->buildId("ios");
            $this->setData("bundle_id", $bundle_id)->save();
        }

        return $bundle_id;
    }

    /**
     * get package_name, create default if empty
     *
     * @return array|mixed|null|string
     */
    public function getPackageName() {
        $package_name = $this->getData("package_name");
        if(empty($package_name)) {
            $package_name = $this->buildId("android");
            $this->setData("package_name", $package_name)->save();
        }

        return $package_name;
    }

    /**
     * Build default id
     *
     * @param string $type
     * @return string
     * @throws Zend_Uri_Exception
     */
    public function buildId($type = "app") {

        $buildId = function($host, $suffix) {

            $url = array_reverse(explode(".", $url));
            $url[] = $suffix;

            foreach($url as &$part) {
                $part = preg_replace("/[^0-9a-z\.]/i", "", $part);
            }

            return implode(".", $url);
        };

        /** Just in case someone messed-up data in backoffice we must have a fallback. */
        if(Siberian::getWhitelabel() !== false) {

            $whitelabel = Siberian::getWhitelabel();

            $id_android = $whitelabel->getData("app_default_identifier_android");
            $id_ios = $whitelabel->getData("app_default_identifier_ios");

            $host = $whitelabel->getHost();

            if(empty($id_android)) {
                $whitelabel->setData("app_default_identifier_android", $buildId($host, "android"));
            }

            if(empty($id_ios)) {
                $whitelabel->setData("app_default_identifier_ios", $buildId($host, "ios"));
            }

            $whitelabel->save();

            $id_android = $whitelabel->getData("app_default_identifier_android");
            $id_ios = $whitelabel->getData("app_default_identifier_ios");

        } else {
            $id_android = System_Model_Config::getValueFor("app_default_identifier_android");
            $id_ios = System_Model_Config::getValueFor("app_default_identifier_ios");

            $request = Zend_Controller_Front::getInstance()->getRequest();
            $host = mb_strtolower($request->getServer("HTTP_HOST"));

            if(empty($id_android)) {
                System_Model_Config::setValueFor("app_default_identifier_android", $buildId($host, "android"));
            }

            if(empty($id_ios)) {
                System_Model_Config::setValueFor("app_default_identifier_ios", $buildId($host, "ios"));
            }

            $id_android = System_Model_Config::getValueFor("app_default_identifier_android");
            $id_ios = System_Model_Config::getValueFor("app_default_identifier_ios");
        }

        // Now we can process bundle id or package name
        switch($type) {
            case "android":
                    return $id_android . $this->getKey();
                break;
            case "ios":
                    return $id_ios . $this->getKey();
                break;
        }
    }

    /**
     * @param $package_name
     * @return string
     */
    public function validatePackageName($package_name) {
        $parts = explode(".", $package_name);
        foreach($parts as $i => $part) {
            if($part == "new") {
                $parts[$i] = "new_";
            }
        }

        return implode(".", $parts);
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

    public function isSomeoneElseEditingIt($admin_id = null) {
        return $this->getTable()->isSomeoneElseEditingIt($this->getId(), Zend_Session::getId(), $admin_id);
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

    public function getUsedOptions() {
        $option = new Application_Model_Option_Value();
        return $option->findAllWithOptionsInfos(array("a.app_id" => $this->getId(), "a.is_visible" => 1));
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

    /**
     * @param int $samples
     * @return Application_Model_Option_Value[]
     */
    public function getPages($samples = 0, $with_folder = false) {

        $options = array(
            "a.app_id"      => $this->getId(),
            "remove_folder" => new Zend_Db_Expr("folder_category_id IS NULL"),
            "is_visible"    => 1
        );

        if($with_folder) {
            unset($options["remove_folder"]);
        }

        if(empty($this->_pages)) {
            $option = new Application_Model_Option_Value();
            $this->_pages = $option->findAll($options);
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
        else return __('My account');
    }

    public function getShortTabbarAccountName() {
        return Core_Model_Lib_String::formatShortName($this->getTabbarAccountName());
    }

    public function getTabbarMoreName() {
        if($this->hasTabbarMoreName()) return $this->getData('tabbar_more_name');
        else return __('More');
    }

    public function getShortTabbarMoreName() {
        return Core_Model_Lib_String::formatShortName($this->getTabbarMoreName());
    }

    public function usesUserAccount() {
        $options = $this->getUsedOptions();
        foreach($options as $option) {
            if($option->getUseMyAccount()) {
                return true;
            }
        }

        return false;
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

    public static function hasModuleInstalled($code) {
        $module = new Installer_Model_Installer_Module();
        $module->prepare($code, false);

        return $module->isInstalled();
    }

    public function getLogo() {
        $logo = self::getImagePath() . $this->getData('logo');
        $baseLogo = self::getBaseImagePath() . $this->getData('logo');
        if (is_file($baseLogo)) {
            return $logo;
        }

        return self::getImagePath() . '/placeholder/no-image.png';
    }

    public function getIcon($size = null, $name = null, $base = false) {

        if (!$size) {
            $size = 114;
        }

        $icon = self::getBaseImagePath().$this->getData('icon');
        if (!is_file($icon) || !file_exists($icon)) {
            $icon = self::getBaseImagePath() . '/placeholder/no-image.png';
            $image = Siberian_Image::open($icon);
            $image->fillBackground(0xf3f3f3);
            return $image->inline('png', 100);
        }

        if (empty($name)) {
            $name = sha1($icon . $size);
        }
        $name = $name . '_' . filesize($icon);

        $newIcon = new Core_Model_Lib_Image();
        $newIcon
            ->setId($name)
            ->setPath($icon)
            ->setWidth($size)
            ->crop();

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

    public function subscriptionIsOffline() {
        if(Siberian_Version::TYPE != "PE") return true;
        return $this->getSubscription()->getPaymentMethod() == "offline";
    }

    public function subscriptionIsDeleted() {
        if(Siberian_Version::TYPE != "PE") return true;
        return $this->getSubscription()->getIsSubscriptionDeleted();
    }

    public function isAvailableForPublishing($check_sources_access_type) {
        $errors = array();
        if($this->getPages()->count() < 3) $errors[] = __("At least, 4 pages in the application");
        if(!$this->getData('background_image')) $errors[] = __("The homepage image");
        if(!$this->getStartupImage()) $errors[] = __("The startup image");
        if(!$this->getData('icon')) $errors[] = __("The desktop icon");
        if(!$this->getName()) $errors[] = __("The application name");
        if($check_sources_access_type) {
            if(!$this->getBundleId()) $errors[] = __("The bundle id");
        } else {
            if(!$this->getDescription()) $errors[] = __("The description");
            else if(strlen($this->getDescription()) < 200) $errors[] = __("At least 200 characters in the description");
            if(!$this->getKeywords()) $errors[] = __("The keywords");
            if(!$this->getMainCategoryId()) $errors[] = __("The main category");
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
            if ($background_image = $this->getData('background_image')) {
                if ($type === 'normal') {
                    $background_image .= '.jpg';
                } else if($type === 'retina') {
                    $background_image .= '@2x.jpg';
                } else if($type === 'retina4') {
                    $background_image .= '-568h@2x.jpg';
                }

                if (file_exists(self::getBaseImagePath() . $background_image)) {
                    $backgroundImage = self::getImagePath() . $background_image;
                }
            }
        } catch (Exception $e) {
            $backgroundImage = '';
        }

        if(empty($backgroundImage)) {
            $backgroundImage = $this->getNoBackgroundImageUrl($type);
        }

        return $backgroundImage;
    }

    public function getHomepageBackgroundImageUrl($type = '', $return = false) {

        try {

            $image = '';

            switch ($type) {
                case "landscape_hd": $image_name = $this->getData('background_image_landscape_hd'); break;
                case "landscape_tablet": $image_name = $this->getData('background_image_landscape_tablet'); break;
                case "landscape_standard":
                case "landscape": $image_name = $this->getData('background_image_landscape'); break;
                case "hd": $image_name = $this->getData('background_image_hd'); break;
                case "tablet": $image_name = $this->getData('background_image_tablet'); break;
                case "standard":
                default: $image_name = $this->getData('background_image'); break;
            }

            if ($return === true) {
                return $image_name;
            }

            // In case we don't have landscape ones for example!!
            if (empty($image_name)) {
                $image_name = $this->getData('background_image');
            }

            if (!empty($image_name)) {
                if (file_exists(self::getBaseImagePath().$image_name)) {
                    $image = self::getImagePath() . $image_name;
                } else if(file_exists(self::getBaseTemplatePath().$image_name)) {
                    $image = self::getTemplatePath() . $image_name;
                }
            }

        } catch (Exception $e) {
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
            $protocol = System_Model_Config::getValueFor("use_https") ? "https://" : "http://";
            $url = Core_Model_Url::createCustom($protocol.$domain, $url, $params, $locale);
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
            ->setData("bundle_id", "")
            ->setData("package_name", "")
            ->setDomain(null)
            ->setSubdomain(null)
            ->setSubdomainIsValidated(null)
            ->save()
        ;

        // Duplicate the images folder
        $old_app_folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath() . DIRECTORY_SEPARATOR . $old_app_id);
        $target_app_folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath() . DIRECTORY_SEPARATOR . $this->getId());
        Core_Model_Directory::duplicate($old_app_folder, $target_app_folder);

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


    /**
     * @param bool $base64
     * @return string
     */
    public function _getImage($name) {
        return $this->__getBase64Image($this->getData($name));
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setImage($name, $base64, $option, $width = 512, $height = 512) {
        $path = $this->__setImageFromBase64($base64, $option, $width, $height);
        $this->setData($name, $path);

        return $this;
    }

    /**
     * Convert application into YAML with base64 images
     *
     * @return string
     */
    public function toYml() {
        $data = $this->getData();

        $data["background_image"]               = $this->_getImage("background_image");
        $data["background_image_hd"]            = $this->_getImage("background_image_hd");
        $data["background_image_tablet"]        = $this->_getImage("background_image_tablet");
        $data["icon"]                           = $this->_getImage("icon");
        $data["startup_image"]                  = $this->_getImage("startup_image");
        $data["startup_image_retina"]           = $this->_getImage("startup_image_retina");
        $data["startup_image_iphone_6"]         = $this->_getImage("startup_image_iphone_6");
        $data["startup_image_iphone_6_plus"]    = $this->_getImage("startup_image_iphone_6_plus");
        $data["startup_image_ipad_retina"]      = $this->_getImage("startup_image_ipad_retina");

        $data["created_at"] = null;
        $data["updated_at"] = null;

        $data["name"] = null;
        $data["bundle_id"] = null;
        $data["key"] = null;

        /** Colors */
        $template_block_app_model = new Template_Model_Block_App();
        $tbas = $template_block_app_model->findAll(array(
            "app_id = ?" => $this->getId(),
        ));

        $dataset_tbas = array();
        foreach($tbas as $tba) {
            $tba_data = $tba->getData();
            $tba_data["created_at"] = null;
            $tba_data["updated_at"] = null;

            $dataset_tbas[] = $tba_data;
        }

        $dataset = array(
            "application" => $data,
            "colors" => $dataset_tbas,
        );

        $dataset = Siberian_Yaml::encode($dataset);

        return $dataset;
    }
}
