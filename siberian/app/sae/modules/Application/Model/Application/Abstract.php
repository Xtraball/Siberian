<?php

use Siberian\Json;

/**
 * Class Application_Model_Application_Abstract
 *
 * @version 4.12.22
 *
 * @method integer getId()
 * @method string getFlickrKey()
 * @method string getFlickrSecret()
 * @method $this setIsActive(boolean $isActive)
 * @method $this setLayoutVisibility(boolean $visibility)
 * @method Application_Model_Db_Table_Application getTable()
 */
abstract class Application_Model_Application_Abstract extends Core_Model_Default
{
    /**
     *
     */
    const PATH_IMAGE = '/images/application';
    /**
     *
     */
    const PATH_TEMPLATES = '/images/templates';
    /**
     *
     */
    const OVERVIEW_PATH = 'overview';
    /**
     *
     */
    const BO_DISPLAYED_PER_PAGE = 1000;
    /**
     *
     */
    const PATH_TO_SOURCE_CODE = "/var/apps/browser/index-prod.html#/";
    /**
     *
     */
    const DESIGN_CODE_ANGULAR = "angular";
    /**
     *
     */
    const DESIGN_CODE_IONIC = "ionic";

    /**
     * @var array
     */
    public static $backButtons = [
        'ion-ios-arrow-back',
        'ion-android-arrow-back',
        'ion-arrow-left-a',
        'ion-arrow-left-b',
        'ion-arrow-left-c',
        'ion-arrow-return-left',
        'ion-chevron-left',
        'ion-ios-arrow-left',
        'ion-ios-arrow-thin-left',
        'ion-ios-undo-outline',
        'ion-ios-undo',
        'ion-reply',
        'ion-home',
        'ion-ios-home-outline',
        'ion-ios-home',
    ];

    /**
     * @var
     */
    protected $_startup_image;
    /**
     * @var
     */
    protected $_customers;
    /**
     * @var
     */
    protected $_options;
    /**
     * @var
     */
    protected $_pages;
    /**
     * @var
     */
    protected $_layout;
    /**
     * @var
     */
    protected $_devices;
    /**
     * @var
     */
    protected $_design;
    /**
     * @var
     */
    protected $_design_blocks;
    /**
     * @var array
     */
    protected $_admin_ids = [];

    /**
     * Application_Model_Application_Abstract constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Application';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getData("name") ?? "";
    }

    /**
     * Testing if a value_id belongs to the current app
     *
     * @todo Allowing cross-app access
     *
     * @param $value_id
     * @return bool
     */
    public function valueIdBelongsTo($value_id)
    {

        # Handle special cases.
        if (in_array($value_id, ["home"])) {
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

    /**
     * @param $host
     * @param null $path
     * @return $this
     */
    public function findByHost($host, $path = null)
    {

        if (!empty($path)) {
            $uri = explode('/', ltrim($path, '/'));
            $i = 0;
            while ($i <= 1) {
                if (!empty($uri[$i])) {
                    $value = $uri[$i];
                    $this->find($value, 'tmp_key');
                    if ($this->getId()) {
                        $this->setUseTmpKey('1');
                        break;
                    }
                }
                $i++;
            }
        }

        if (!$this->getId()) {

            if (!in_array($host[0], ['www'])) {
                $this->find($host, 'domain');
            }
        }

        return $this;

    }

    /**
     * @param $admin_id
     * @param array $where
     * @param null $order
     * @param null $count
     * @param null $offset
     * @return Application_Model_Application[]
     */
    public function findAllByAdmin($admin_id, $where = [], $order = null, $count = null, $offset = null)
    {
        return $this->getTable()->findAllByAdmin($admin_id, $where, $order, $count, $offset);
    }

    /**
     * @return mixed
     */
    public function findAllToPublish()
    {
        return $this->getTable()->findAllToPublish();
    }

    /**
     * @return Admin_Model_Admin
     * @throws Zend_Exception
     */
    public function getOwner()
    {

        $admin = new Admin_Model_Admin();
        $admin->find($this->getAdminId());
        return $admin;

    }

    /**
     * @return mixed
     */
    public function getPrivacyPolicy()
    {
        $data = $this->getData("privacy_policy");
        $data = trim(strip_tags($data));
        if (empty($data)) {
            $config_pp = __get("privacy_policy");
            $this->setData("privacy_policy", $config_pp)->save();
        }

        return $this->getData("privacy_policy");
    }

    /**
     * @return $this
     * @throws Zend_Uri_Exception
     * @throws \Siberian\Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function save()
    {

        if (!$this->getId()) {

            // Check if values are valid!
            $applicationName = $this->getName();
            if (empty($applicationName)) {
                throw new \Siberian\Exception(__('Name is required to save the Application.'));
            }

            // Force trim name on save.
            $this->setName($this->getName());

            if (!Siberian_Version::is('sae')) {
                $adminId = trim($this->getData('admin_id'));
                if (empty($adminId) || ($adminId === 0) || ($adminId === '0')) {
                    throw new \Siberian\Exception(__('AdminId is required to save the Application.'));
                }
            }

            $this->setKey(uniqid());

            // Check bundle/package
            $this->getBundleId();
            $this->getPackageName();

            $this->setDesignCode(self::DESIGN_CODE_IONIC);

            if (!$this->getLayoutId()) {
                $this->setLayoutId(1);
            }
        }

        parent::save();

        if (!empty($this->_admin_ids)) {
            foreach ($this->_admin_ids as $admin_id) {
                $this->getTable()->addAdmin($this->getId(), $admin_id);
            }
        }

        return $this;

    }

    /**
     * @param $name
     * @return $this
     * @throws \Siberian\Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function setName($name)
    {
        $name = \rock\sanitize\Sanitize::removeTags()->sanitize(trim($name));

        if (is_numeric(substr($name, 0, 1))) {
            throw new \Siberian\Exception(p__("application", "The application's name cannot start with a number"));
        }

        if (strlen($name) < 6) {
            throw new \Siberian\Exception(p__("application", "The application's name must be at least 6 characters long."));
        }

        return $this->setData('name', $name);
    }

    /**
     * @return mixed
     * @throws \rock\sanitize\SanitizeException
     */
    public function getName()
    {
        return \rock\sanitize\Sanitize::removeTags()->sanitize(trim($this->getData('name')));
    }

    /**
     * @param $description
     * @return mixed
     * @throws \Siberian\Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function setDescription($description)
    {
        if (strlen($description) < 200) {
            throw new \Siberian\Exception(p__("application","The description must be at least 200 characters"));
        }

        $description = \rock\sanitize\Sanitize::removeTags()->sanitize($description);

        return $this->setData('description', $description);
    }

    /**
     * @return array|mixed|null|string
     */
    public function getDescription()
    {
        return $this->getData('description');
    }

    /**
     * @param string $keywords
     * @return $this
     * @throws \rock\sanitize\SanitizeException
     */
    public function setKeywords($keywords)
    {
        $keywords = \rock\sanitize\Sanitize::removeTags()->sanitize($keywords);

        return $this->setData('keywords', $keywords);
    }

    /**
     * @param $mainCategoryId
     * @return $this
     */
    public function setMainCategoryId($mainCategoryId)
    {
        return $this->setData('main_category_id', $mainCategoryId);
    }

    /**
     * @param $secondaryCategoryId
     * @return $this
     */
    public function setSecondaryCategoryId($secondaryCategoryId)
    {
        return $this->setData('secondary_category_id', $secondaryCategoryId);
    }

    /**
     * @param string $bundleId
     * @return $this
     * @throws Exception
     * @throws \Siberian\Exception
     */
    public function setBundleId($bundleId)
    {
        $regexIos = "/^([a-z]){2,10}\.([a-z-]{1}[a-z0-9-]*){1,30}((\.([a-z-]{1}[a-z0-9-]*){1,61})*)?$/i";

        if (preg_match($regexIos, $bundleId)) {
            $this->setData('bundle_id', $bundleId)->save();
        } else {
            throw new \Siberian\Exception(__("Your bundle id is invalid, format should looks like com.mydomain.iosid"));
        }

        return $this;
    }

    /**
     * @param string $packageName
     * @return $this
     * @throws Exception
     * @throws \Siberian\Exception
     */
    public function setPackageName($packageName)
    {
        $regexAndroid = "/^([a-z]{1}[a-z_]*){2,10}\.([a-z]{1}[a-z0-9_]*){1,30}((\.([a-z]{1}[a-z0-9_]*){1,61})*)?$/i";

        if (preg_match($regexAndroid, $packageName)) {
            $this->setData('package_name', $this->validatePackageName($packageName))->save();
        } else {
            throw new \Siberian\Exception(__("Your package name is invalid, format should looks like com.mydomain.uuid"));
        }

        return $this;
    }

    /**
     * @param string $privacyPolicy
     * @return $this
     * @throws Zend_Exception
     */
    public function setPrivacyPolicy($privacyPolicy)
    {
        $_filtered = \Siberian\Xss::sanitize($privacyPolicy);

        return $this->setData('privacy_policy', $_filtered);
    }

    /**
     * @param string $id
     * @return $this
     * @throws Zend_Exception
     */
    public function setFacebookId($id)
    {
        $_filtered = \Siberian\Xss::sanitize($id);

        return $this->setData('facebook_id', $_filtered);
    }

    /**
     * @param string $key
     * @return $this
     * @throws Zend_Exception
     */
    public function setFacebookKey($key)
    {
        $_filtered = \Siberian\Xss::sanitize($key);

        return $this->setData('facebook_key', $_filtered);
    }

    /**
     * @param string $appId
     * @return $this
     * @throws Zend_Exception
     */
    public function setOnesignalAndroidAppId($appId)
    {
        $_filtered = \Siberian\Xss::sanitize($appId);

        return $this->setData('onesignal_android_app_id', $_filtered);
    }

    /**
     * @param string $appKeytoken
     * @return $this
     * @throws Zend_Exception
     */
    public function setOnesignalAndroidAppKeyToken($appKeytoken)
    {
        $_filtered = \Siberian\Xss::sanitize($appKeytoken);

        return $this->setData('onesignal_android_app_key_token', $_filtered);
    }

    /**
     * @param string $appId
     * @return $this
     * @throws Zend_Exception
     */
    public function setOnesignalAppId($appId)
    {
        $_filtered = \Siberian\Xss::sanitize($appId);

        return $this->setData('onesignal_app_id', $_filtered);
    }

    /**
     * @param string $appKeytoken
     * @return $this
     * @throws Zend_Exception
     */
    public function setOnesignalAppKeyToken($appKeytoken)
    {
        $_filtered = \Siberian\Xss::sanitize($appKeytoken);

        return $this->setData('onesignal_app_key_token', $_filtered);
    }

    /**
     * @param $segment
     * @return mixed
     * @throws Zend_Exception
     */
    public function setOnesignalDefaultSegment($segment)
    {
        $_filtered = \Siberian\Xss::sanitize($segment);

        return $this->setData('onesignal_default_segment', $_filtered);
    }

    /**
     * @param string $appId
     * @return $this
     * @throws Zend_Exception
     */
    public function setOnesignalIosAppId($appId)
    {
        $_filtered = \Siberian\Xss::sanitize($appId);

        return $this->setData('onesignal_ios_app_id', $_filtered);
    }

    /**
     * @param string $appKeytoken
     * @return $this
     * @throws Zend_Exception
     */
    public function setOnesignalIosAppKeyToken($appKeytoken)
    {
        $_filtered = \Siberian\Xss::sanitize($appKeytoken);

        return $this->setData('onesignal_ios_app_key_token', $_filtered);
    }

    /**
     * @param string $consumerKey
     * @return $this
     * @throws Zend_Exception
     */
    public function setTwitterConsumerKey($consumerKey)
    {
        $_filtered = \Siberian\Xss::sanitize($consumerKey);

        return $this->setData('twitter_consumer_key', $_filtered);
    }

    /**
     * @param string $consumerSecret
     * @return $this
     * @throws Zend_Exception
     */
    public function setTwitterConsumerSecret($consumerSecret)
    {
        $_filtered = \Siberian\Xss::sanitize($consumerSecret);

        return $this->setData('twitter_consumer_secret', $_filtered);
    }

    /**
     * @param string $apiToken
     * @return $this
     * @throws Zend_Exception
     */
    public function setTwitterApiToken($apiToken)
    {
        $_filtered = \Siberian\Xss::sanitize($apiToken);

        return $this->setData('twitter_api_token', $_filtered);
    }

    /**
     * @param string $apiSecret
     * @return $this
     * @throws Zend_Exception
     */
    public function setTwitterApiSecret($apiSecret)
    {
        $_filtered = \Siberian\Xss::sanitize($apiSecret);

        return $this->setData('twitter_api_secret', $_filtered);
    }

    /**
     * @param string $clientId
     * @return $this
     * @throws Zend_Exception
     */
    public function setInstagramClientId($clientId)
    {
        $_filtered = \Siberian\Xss::sanitize($clientId);

        return $this->setData('instagram_client_id', $_filtered);
    }

    /**
     * @param string $token
     * @return $this
     * @throws Zend_Exception
     */
    public function setInstagramToken($token)
    {
        $_filtered = \Siberian\Xss::sanitize($token);

        return $this->setData('instagram_token', $_filtered);
    }

    /**
     * @param string $key
     * @return $this
     * @throws Zend_Exception
     */
    public function setFlickrKey($key)
    {
        $_filtered = \Siberian\Xss::sanitize($key);

        return $this->setData('flickr_key', $_filtered);
    }

    /**
     * @param string $secret
     * @return $this
     * @throws Zend_Exception
     */
    public function setFlickrSecret($secret)
    {
        $_filtered = \Siberian\Xss::sanitize($secret);

        return $this->setData('flickr_secret', $_filtered);
    }

    /**
     * @param string $key
     * @return $this
     * @throws Zend_Exception
     */
    public function setGooglemapsKey($key)
    {
        $_filtered = \Siberian\Xss::sanitize($key);

        return $this->setData('googlemaps_key', $_filtered);
    }


    /**
     * @param $admin
     * @return $this
     */
    public function addAdmin($admin)
    {
        if ($this->getId()) {
            $is_allowed_to_add_pages = $admin->hasIsAllowedToAddPages() ? $admin->getIsAllowedToAddPages() : true;
            $this->getTable()->addAdmin($this->getId(), $admin->getId(), $is_allowed_to_add_pages);
        } else {
            if (!in_array($admin->getId(), $this->_admin_ids)) {
                $this->_admin_ids[] = $admin->getId();
            }
        }

        return $this;
    }

    /**
     * @param $admin
     * @return $this
     */
    public function removeAdmin($admin)
    {
        $this->getTable()->removeAdmin($this->getId(), $admin->getId());
        return $this;
    }

    /**
     * @param $adminIds
     * @return $this
     */
    public function setAdminIds($adminIds)
    {
        $this->_admin_ids = $adminIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdmins()
    {
        return $this->getTable()->getAdminIds($this->getId());
    }

    /**
     * @param $admin_id
     * @return bool
     */
    public function hasAsAdmin($admin_id)
    {
        return (bool)$this->getTable()->hasAsAdmin($this->getId(), $admin_id);
    }

    /**
     * @return mixed
     */
    public function getDevices()
    {
        $device_ids = array_keys(Application_Model_Device::getAllIds());
        foreach ($device_ids as $device_id) {
            if (empty($this->_devices[$device_id])) {
                $this->getDevice($device_id);
            }
        }

        return $this->_devices;
    }

    /**
     * @param $deviceId
     * @return Application_Model_Device_Ionic_Android|Application_Model_Device_Ionic_Ios
     */
    public function getDevice($deviceId)
    {
        if (empty($this->_devices[$deviceId])) {
            $device = new Application_Model_Device();
            $device->find([
                'app_id' => $this->getId(),
                'type_id' => $deviceId
            ]);
            if (!$device->getId()) {
                $device->loadDefault($deviceId);
                $device->setAppId($this->getId());

                // Save newly created device!
                $device->save();

                // Fetch it again (for MySQL defaults)
                $device->find($device->getId());
            }
            $device->setDesignCode($this->getDesignCode());
            $this->_devices[$deviceId] = $device;
        }

        return $this->_devices[$deviceId];
    }

    /**
     * @return Application_Model_Device_Ionic_Android
     */
    public function getAndroidDevice()
    {
        return $this->getDevice(2);
    }

    /**
     * @return Application_Model_Device_Ionic_Ios
     */
    public function getIosDevice()
    {
        return $this->getDevice(1);
    }

    /**
     * @return bool
     */
    public function useIonicDesign()
    {
        return true;
    }

    /**
     * Enforce "ionic" as design code to prevent db "angular"
     * @return string
     */
    public function getDesignCode ()
    {
        return self::DESIGN_CODE_IONIC;
    }

    /**
     * @return Template_Model_Design
     * @throws Zend_Exception
     */
    public function getDesign()
    {
        if (!$this->_design) {
            $this->_design = new Template_Model_Design();
            if ($this->getDesignId()) {
                $this->_design->find($this->getDesignId());
            }
        }

        return $this->_design;
    }

    /**
     * @param $design
     * @param null $category
     * @return $this
     * @throws Exception
     */
    public function setDesign($design, $category = null)
    {
        if ($design->getVersion() == 2) {
            $this->setSplashVersion(2);
            $this->setDesignUnified($design, $category);
        } else {
            $image_name = uniqid() . '.png';
            $relative_path = '/homepage_image/bg/';
            $lowres_relative_path = '/homepage_image/bg_lowres/';

            if (!is_dir(self::getBaseImagePath() . $lowres_relative_path)) {
                mkdir(self::getBaseImagePath() . $lowres_relative_path, 0777, true);
            }

            if (!copy($design->getBackgroundImage(true), self::getBaseImagePath() . $lowres_relative_path . $image_name)) {
                throw new Exception(__('#101: An error occurred while saving'));
            }

            if (!is_dir(self::getBaseImagePath() . $relative_path)) {
                mkdir(self::getBaseImagePath() . $relative_path, 0777, true);
            }
            if (!copy($design->getBackgroundImageHd(true), self::getBaseImagePath() . $relative_path . $image_name)) {
                throw new Exception(__('#102: An error occurred while saving'));
            }

            foreach ($design->getBlocks() as $block) {
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
                ->setStartupImageIphoneX($design->getStartupImageIphoneX())
                ->setHomepageBackgroundImageRetinaLink($relative_path . $image_name)
                ->setHomepageBackgroundImageLink($lowres_relative_path . $image_name);

            if (!$this->getOptionIds() AND $category->getId()) {
                $this->createDummyContents($category, null, $category);
            }
        }

        return $this;
    }

    /**
     * @param Template_Model_Design $design
     * @param null $category
     * @throws Exception
     */
    public function setDesignUnified($design, $category = null)
    {
        $image_name = uniqid() . '.png';
        $relative_path = '/homepage_image/bg/';
        $lowres_relative_path = '/homepage_image/bg_lowres/';

        if (!is_dir(self::getBaseImagePath() . $lowres_relative_path)) {
            mkdir(self::getBaseImagePath() . $lowres_relative_path, 0777, true);
        }

        if (!is_dir(self::getBaseImagePath() . $relative_path)) {
            mkdir(self::getBaseImagePath() . $relative_path, 0777, true);
        }

        foreach ($design->getBlocks() as $block) {
            $block
                ->setAppId($this->getId())
                ->save();
        }

        // Here we duplicate the icon to preserve it!

        $this
            ->setDesignId($design->getId())
            ->setLayoutId($design->getLayoutId())
            ->setLayoutVisibility($design->getLayoutVisibility())
            ->setOverView($design->getOverview())
            ->setBackgroundImageUnified($design->getBackgroundImageUnified())
            ->setIcon($design->getIcon())
            ->setStartupImageUnified($design->getStartupImageUnified());

        if (!$this->getOptionIds() && $category->getId()) {
            $this->createDummyContents($category, null, $category);
        }
    }

    /**
     * @return string
     * @throws Zend_Exception
     */
    public function getRealLayoutVisibility()
    {
        $layout = $this->getLayout();
        $layout_visibility = $layout->getVisibility();
        $layout_code = $layout->getCode();
        if ($layout_code === "layout_9") {
            return "toggle";
        }

        if ($layout_visibility === "always") {

        }
    }

    /**
     * @param $category
     * @param null $design
     * @param null $_category
     * @throws Exception
     */
    public function createDummyContents($category, $design = null, $_category = null)
    {
        try {
            $design = is_null($design) ? $this->getDesign() : $design;
            $design_content = new Template_Model_Design_Content();
            $design_contents = $design_content->findAll(['design_id' => $design->getDesignId()]);

            foreach ($design_contents as $content) {
                $option_value = new Application_Model_Option_Value();
                $option = new Application_Model_Option();
                $option->find($content->getOptionId());

                // Feaeture doesn't exists!
                if (!$option->getId()) {
                    continue;
                }

                // Feaeture is globally disabled!
                if (!$option->getIsEnabled()) {
                    continue;
                }

                // User don't have access to feature
                $aclList = \Admin_Controller_Default::_sGetAcl();
                if ($aclList && !$aclList->isAllowed('feature_' . $option->getCode())) {
                    continue;
                }

                $option_value
                    ->setOptionId($content->getOptionId())
                    ->setAppId($this->getApplication()->getId())
                    ->setTabbarName($content->getOptionTabbarName())
                    ->setIconId($content->getOptionIcon())
                    ->setBackgroundImage($content->getOptionBackgroundImage())
                    ->save();

                if ($option->getModel() && $option->getCode() !== 'push_notification') {
                    $category = ($_category != null) ? $_category : $category;
                    $option->getObject()->createDummyContents($option_value, $design, $category);
                }
            }
        } catch (\Exception $e) {
            // Silently continue!
        }
    }

    /**
     * @param null $type_id
     * @return Template_Model_Block[]
     */
    public function getBlocks($type_id = null)
    {

        if (!$type_id) {
            $type_id = $this->useIonicDesign() ? Template_Model_Block::TYPE_IONIC_APP : Template_Model_Block::TYPE_APP;
        }

        $block = new Template_Model_Block();
        if (empty($this->_design_blocks)) {
            $this->_design_blocks = $block->findAll(['app_id' => $this->getId(), 'type_id' => $type_id], 'position ASC');

            if (!empty($this->_design_blocks)) {
                foreach ($this->_design_blocks as $block) {
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
    public function getBlock($code)
    {

        if ($this->useIonicDesign() && $code === 'tabbar') {
            $code = 'homepage';
        }
        $blocks = $this->getBlocks();

        foreach ($blocks as $block) {
            if ($block->getCode() === $code) {
                return $block;
            } else if ($block->getChildren()) {
                foreach ($block->getChildren() as $child) {
                    if ($child->getCode() === $code) {
                        return $child;
                    }
                }
            }
        }

        return (new Template_Model_Block());
    }

    /**
     * @param $blocks
     * @return $this
     */
    public function setBlocks($blocks)
    {
        $this->_design_blocks = $blocks;
        return $this;
    }

    /**
     * @return Application_Model_Layout_Homepage
     * @throws Zend_Exception
     */
    public function getLayout()
    {

        if (!$this->_layout) {
            $this->_layout = new Application_Model_Layout_Homepage();
            $this->_layout->find($this->getLayoutId());
        }

        return $this->_layout;

    }

    /**
     * @return array|mixed|null|string
     * @throws Zend_Uri_Exception
     * @throws \Siberian\Exception
     */
    public function getBundleId()
    {
        $bundleId = $this->getData('bundle_id');
        if (empty($bundleId)) {
            $bundleId = $this->buildId('ios');
            $this->setData('bundle_id', $bundleId)->save();
        }

        return $bundleId;
    }

    /**
     * @return array|mixed|null|string
     * @throws Zend_Uri_Exception
     * @throws \Siberian\Exception
     */
    public function getPackageName()
    {
        $packageName = $this->getData('package_name');
        if (empty($packageName)) {
            $packageName = $this->buildId('android');
            $this->setData('package_name', $packageName)->save();
        }

        return $packageName;
    }

    /**
     * Build default id
     *
     * @param string $type
     * @return string
     * @throws Zend_Uri_Exception
     */
    public function buildId($type = "app")
    {
        $buildId = function ($host, $suffix) {

            $url = array_reverse(explode(".", $host));
            $url[] = $suffix;

            foreach ($url as &$part) {
                $part = preg_replace("/[^0-9a-z\.]/i", "", $part);
            }

            return implode_polyfill(".", $url);
        };

        /** Just in case someone messed-up data in backoffice we must have a fallback. */
        if (Siberian::getWhitelabel() !== false) {

            $whitelabel = Siberian::getWhitelabel();

            $id_android = $whitelabel->getData("app_default_identifier_android");
            $id_ios = $whitelabel->getData("app_default_identifier_ios");

            $host = $whitelabel->getHost();

            if (empty($id_android)) {
                $whitelabel->setData("app_default_identifier_android", $buildId($host, "and"));
            }

            if (empty($id_ios)) {
                $whitelabel->setData("app_default_identifier_ios", $buildId($host, "ios"));
            }

            $whitelabel->save();

            $id_android = $whitelabel->getData("app_default_identifier_android");
            $id_ios = $whitelabel->getData("app_default_identifier_ios");

        } else {
            $id_android = __get("app_default_identifier_android");
            $id_ios = __get("app_default_identifier_ios");

            $request = Zend_Controller_Front::getInstance()->getRequest();
            $host = mb_strtolower($request->getServer("HTTP_HOST"));

            if (empty($id_android)) {
                __set("app_default_identifier_android", $buildId($host, "and"));
            }

            if (empty($id_ios)) {
                __set("app_default_identifier_ios", $buildId($host, "ios"));
            }

            $id_android = __get("app_default_identifier_android");
            $id_ios = __get("app_default_identifier_ios");
        }

        // Now we can process bundle id or package name
        switch ($type) {
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
    public function validatePackageName($package_name)
    {
        $parts = explode(".", $package_name);
        foreach ($parts as $i => $part) {
            if ($part == "new") {
                $parts[$i] = "new_";
            }
        }

        return implode_polyfill(".", $parts);
    }

    /**
     * @return array|bool|mixed|null|string
     */
    public function isActive()
    {
        return (bool)$this->getData("is_active");
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return (bool)$this->getData("is_locked");
    }

    /**
     * @return bool
     */
    public function canBePublished()
    {
        return (bool)$this->getData("can_be_published");
    }

    /**
     * @param null $admin_id
     * @return mixed
     */
    public function isSomeoneElseEditingIt($admin_id = null)
    {
        return $this->getTable()->isSomeoneElseEditingIt($this->getId(), Zend_Session::getId(), $admin_id);
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {

        if (is_null($this->_customers)) {
            $customer = new Customer_Model_Customer();
            $this->_customers = $customer->findAll(["app_id" => $this->getId()]);
        }

        return $this->_customers;

    }

    /**
     * @param bool $isVisible deprecated
     * @return Application_Model_Option_Value[]
     * @throws \Siberian\Exception
     */
    public function getOptions($isVisible = true)
    {
        if (empty($this->_options)) {
            $query = [
                "a.app_id" => $this->getId()
            ];

            $this->_options = (new Application_Model_Option_Value())->findAll($query);
        }

        // Check if customer account is required
        $this->checkCustomerAccount();

        return $this->_options;
    }

    /**
     * @return Application_Model_Option_Value|bool|null
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function checkCustomerAccount ()
    {
        $useMyAccount = false;
        if (is_array($this->_options)) {
            foreach ($this->_options as $option) {
                if ($option->getUseMyAccount() == 1) {
                    $useMyAccount = true;
                    break;
                }
            }
        }

        if ($useMyAccount) {
            $option = (new Application_Model_Option())->find("tabbar_account", "code");
            if (!$option->getId()) {
                throw new \Siberian\Exception(__("My account feature is missing!"));
            }

            $customerAccount = (new Application_Model_Option_Value())
                ->find(
                    [
                        "app_id" => $this->getId(),
                        "option_id" => $option->getId()
                    ]);

            if (!$customerAccount->getId()) {

                // Create the account feature
                $customerAccount
                    ->setAppId($this->getId())
                    ->setTabbarName(__($option->getName()))
                    ->setOptionId($option->getId())
                    ->setPosition($customerAccount->getPosition() ? $customerAccount->getPosition() : 0)
                    ->setIsvisible(1)
                    ->setIconId($option->getDefaultIconId())
                    ->setSettings(Json::encode([
                        'enable_facebook_login' => true,
                        'enable_registration' => true,
                        'enable_commercial_agreement' => true,
                        'enable_commercial_agreement_label' => '',
                        'enable_password_verification' => false,
                    ]))
                    ->save();

                $this->_options = (new Application_Model_Option_Value())
                    ->findAll(["a.app_id" => $this->getId()]);
            }
            return $customerAccount;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getUsedOptions()
    {
        $option = new Application_Model_Option_Value();
        return $option->findAllWithOptionsInfos(["a.app_id" => $this->getId()]);
    }

    /**
     * @return array
     * @throws \Siberian\Exception
     */
    public function getOptionIds()
    {

        $option_ids = [];
        $options = $this->getOptions();
        foreach ($options as $option) {
            $option_ids[] = $option->getOptionId();
        }

        return $option_ids;

    }

    /**
     * @param $code
     * @return Application_Model_Option|Application_Model_Option_Value
     */
    public function getOption($code)
    {

        $option_sought = new Application_Model_Option();
        $dummy = new Application_Model_Option();
        $dummy->find($code, 'code');
        foreach ($this->getOptions() as $page) {
            if ($page->getOptionId() == $dummy->getId()) $option_sought = $page;
        }

        return $option_sought;

    }

    /**
     * @param int $samples
     * @param bool $with_folder
     * @return Application_Model_Option_Value[]
     * @throws \Siberian\Exception
     */
    public function getPages($samples = 0, $with_folder = false)
    {

        $options = [
            "a.app_id" => $this->getId(),
            "remove_folder" => new Zend_Db_Expr("folder_category_id IS NULL")
        ];

        if ($with_folder) {
            unset($options["remove_folder"]);
        }

        // Ensure all options are up!
        $this->getOptions();

        if (empty($this->_pages)) {
            $option = new Application_Model_Option_Value();
            $this->_pages = $option->findAll($options);
        }

        if ($this->_pages->count() == 0 AND $samples > 0) {
            $dummy = Application_Model_Option_Value::getDummy();
            for ($i = 0; $i < $samples; $i++) {
                $this->_pages->addRow($this->_pages->count(), $dummy);
            }
        }

        return $this->_pages;

    }

    /**
     * @param $code
     * @return $this|null
     */
    public function getPage($code)
    {

        $dummy = new Application_Model_Option();
        $dummy->find($code, 'code');

        $page_sought = new Application_Model_Option_Value();
        return $page_sought->find(['app_id' => $this->getId(), 'option_id' => $dummy->getId()]);

    }

    /**
     * @return Application_Model_Option_Value
     * @throws Zend_Session_Exception
     */
    public function getFirstActivePage()
    {
        foreach ($this->getPages() as $page) {
            if ($page->isActive()) {
                if ($page->getCode() != "padlock" AND (!$page->isLocked() OR $this->getSession()->getCustomer()->canAccessLockedFeatures())) {
                    return $page;
                }
            }
        }
        return new Application_Model_Option_Value();
    }

    /**
     * @return array|mixed|null|string
     */
    public function getTabbarAccountName()
    {
        if ($this->hasTabbarAccountName()) {
            return $this->getData('tabbar_account_name');
        } else {
            return __('My account');
        }
    }

    /**
     * @return string
     */
    public function getShortTabbarAccountName()
    {
        return Core_Model_Lib_String::formatShortName($this->getTabbarAccountName());
    }

    /**
     * @return array|mixed|null|string
     */
    public function getTabbarMoreName()
    {
        if ($this->hasTabbarMoreName()) {
            return $this->getData('tabbar_more_name');
        } else {
            return __('More');
        }
    }

    /**
     * @return string
     */
    public function getShortTabbarMoreName()
    {
        return Core_Model_Lib_String::formatShortName($this->getTabbarMoreName());
    }

    /**
     * @return bool
     */
    public function usesUserAccount()
    {
        /**
         * @var $options Application_Model_Option_Value[]
         */
        $options = $this->getUsedOptions();
        foreach ($options as $option) {
            if ($option->getUseMyAccount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return $this|bool|null
     * @throws \Siberian\Exception
     */
    public function getMyAccount ()
    {
        return $this->checkCustomerAccount();
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     */
    public function getCountryCode()
    {
        $code = $this->getData('country_code');
        if (is_null($code)) {
            $code = Core_Model_Language::getCurrentLocaleCode();
        }
        return $code;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        foreach ($this->getDevices() as $device) {
            if ($device->isPublished()) return true;
        }

        return false;
    }

    /**
     * @param null $uri
     * @param array $params
     * @return string
     * @throws \rock\sanitize\SanitizeException
     */
    public function getQrcode($uri = null, $params = [])
    {
        $qrcode = new Core_Model_Lib_Qrcode();
        $url = "";
        if (filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $url = $uri;
        } else {
            //$url = $this->getUrl($uri);
            $url = $this->getBaseUrl() . $this->getPath("application/device/check", ["app_id" => $this->getAppId()]);
        }

        return $qrcode->getImage($this->getName(), $url, $params);
    }

    /**
     * @return string
     */
    public static function getImagePath()
    {
        return Core_Model_Directory::getPathTo(static::PATH_IMAGE);
    }

    /**
     * @return string
     */
    public static function getBaseImagePath()
    {
        return path(static::PATH_IMAGE);
    }

    /**
     * @return string
     */
    public static function getTemplatePath()
    {
        return Core_Model_Directory::getPathTo(self::PATH_TEMPLATES);
    }

    /**
     * @return string
     */
    public static function getBaseTemplatePath()
    {
        return path(self::PATH_TEMPLATES);
    }

    /**
     * @return array
     */
    public static function getDesignCodes()
    {
        return [
            self::DESIGN_CODE_ANGULAR => ucfirst(self::DESIGN_CODE_ANGULAR),
            self::DESIGN_CODE_IONIC => ucfirst(self::DESIGN_CODE_IONIC)
        ];
    }

    /**
     * @param $code
     * @return bool
     * @throws Zend_Exception
     */
    public static function hasModuleInstalled($code)
    {
        $module = (new Installer_Model_Installer_Module())
            ->prepare($code, false);

        return $module->isInstalled();
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        $logo = self::getImagePath() . $this->getData("logo");
        $baseLogo = self::getBaseImagePath() . $this->getData("logo");
        if (is_file($baseLogo)) {
            return $logo;
        }

        return self::getImagePath() . '/placeholder/no-image.png';
    }

    /**
     * @param null $size
     * @param null $name
     * @param bool $base
     * @return string
     */
    public function getIcon($size = null, $name = null, $base = false)
    {
        if (!$size) {
            $size = 114;
        }

        $icon = self::getBaseImagePath() . $this->getData('icon');
        if (!is_readable($icon) || !is_file($icon)) {
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

    /**
     * @param null $size
     * @return string
     */
    public function getIconUrl($size = null)
    {
        $icon = $this->getIcon($size);
        if (substr($icon, 0, 1) == "/") $icon = substr($icon, 1, strlen($icon) - 1);
        return Core_Model_Url::create() . $icon;
    }

    /**
     * @return array
     * @throws Zend_Exception
     */
    public function getAllPictos()
    {
        $picto_urls = [];
        foreach ($this->getBlocks() as $block) {
            $dir = Core_Model_Directory::getDesignPath(true, "/images/pictos/", "mobile");
            $pictos = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, 4096),
                RecursiveIteratorIterator::SELF_FIRST);
            foreach ($pictos as $picto) {
                $colorized_color = Core_Model_Lib_Image::getColorizedUrl($picto->getPathName(), $block->getColor());
                $colorized_background_color = Core_Model_Lib_Image::getColorizedUrl($picto->getPathName(), $block->getBackgroundColor());
                $picto_urls[$colorized_color] = $colorized_color;
                $picto_urls[$colorized_background_color] = $colorized_background_color;
            }
        }

        return $picto_urls;
    }

    /**
     * @param bool $base
     * @return string
     */
    public function getAppStoreIcon($base = false)
    {
        return $this->getIcon(1024, 'touch_icon_' . $this->getId() . '_1024', $base);
    }

    /**
     * @param bool $base
     * @return string
     */
    public function getGooglePlayIcon($base = false)
    {
        return $this->getIcon(512, 'touch_icon_' . $this->getId() . '_512', $base);
    }

    /**
     * @param string $type
     * @param bool $base
     * @return string
     */
    public function getStartupImageUrl($type = "standard", $base = false)
    {
        if ($this->getSplashVersion() == 2) {
            return $this->getStartupImageUnified();
        }

        try {
            $image = "";

            if ($type === "standard") {
                $image_name = $this->getData("startup_image");
            } else {
                $image_name = $this->getData("startup_image_" . $type);
            }

            if (!empty($image_name) && file_exists(self::getBaseImagePath() . $image_name)) {
                $image = $base ? self::getBaseImagePath() . $image_name : self::getImagePath() . $image_name;
            }
        } catch (Exception $e) {
            $image = '';
        }

        if (empty($image)) {
            $image = $this->getNoStartupImageUrl($type, $base);
        }

        return $image;
    }

    /**
     * @param string $type
     * @param bool $base
     * @return string
     */
    public function getNoStartupImageUrl($type = 'standard', $base = false)
    {
        if ($type == "standard") {
            $type = "";
        } else {
            $type = "-" . str_replace("_", "-", $type);
        }

        $image_name = "no-startupimage{$type}.png";

        $path = $base ? self::getBaseImagePath() : self::getImagePath();

        return $path . "/placeholder/" . $image_name;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        $name = $this->getName();
        if ($name && (mb_strlen($name, 'UTF-8') > 11)) {
            $name = trim(mb_substr($name, 0, 10, "UTF-8")) . '...';
        }

        return $name;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getFacebookId()
    {
        $facebook_app_id = $this->getData("facebook_id");

        if (!$facebook_app_id) {
            $facebook_app_id = Api_Model_Key::findKeysFor('facebook')->getAppId();
        }

        return $facebook_app_id;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getFacebookKey()
    {
        $facebook_key = $this->getData("facebook_key");

        if (!$facebook_key) {
            $facebook_key = Api_Model_Key::findKeysFor('facebook')->getSecretKey();
        }

        return $facebook_key;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getInstagramClientId()
    {
        $instagram_client_id = $this->getData("instagram_client_id");

        if (!$instagram_client_id) {
            $instagram_client_id = Api_Model_Key::findKeysFor('instagram')->getClientId();
        }

        return $instagram_client_id;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getInstagramToken()
    {

        $instagram_token = $this->getData("instagram_token");

        if (!$instagram_token) {
            $instagram_token = Api_Model_Key::findKeysFor('instagram')->getToken();
        }

        return $instagram_token;
    }

    /**
     * @param $positions
     * @return $this
     */
    public function updateOptionValuesPosition($positions)
    {
        $this->getTable()->updateOptionValuesPosition($positions);
        return $this;
    }

    /**
     * SAE & MAE have no subscriptions, we assume that it's always active then.
     *
     * @return bool
     */
    public function subscriptionIsActive()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function subscriptionIsOffline()
    {
        if (Siberian_Version::TYPE != "PE") return true;
        return $this->getSubscription()->getPaymentMethod() == "offline";
    }

    /**
     * @return bool
     */
    public function subscriptionIsDeleted()
    {
        if (Siberian_Version::TYPE != "PE") return true;
        return $this->getSubscription()->getIsSubscriptionDeleted();
    }

    /**
     * @param $checkSourcesAccessType
     * @return array
     */
    public function isAvailableForPublishing($checkSourcesAccessType)
    {
        if ($this->getSplashVersion() == '2') {
            return $this->isAvailableForPublishingUnified($checkSourcesAccessType);
        } else {
            $errors = [];
            if ($this->getPages()->count() < 1) {
                $errors[] = __("At least, 1 page in the application");
            }
            if (!$this->getData('background_image')) {
                $errors[] = __("The homepage image");
            }
            if (!$this->getStartupImage()) {
                $errors[] = __("The startup image");
            }
            if (!$this->getData('icon')) {
                $errors[] = __("The desktop icon");
            }
            if (!$this->getName()) {
                $errors[] = __("The application name");
            }
            if ($checkSourcesAccessType) {
                if (!$this->getBundleId()) $errors[] = __("The bundle id");
            } else {
                if (!$this->getDescription()) {
                    $errors[] = __("The description");
                } else if (strlen($this->getDescription()) < 200) {
                    $errors[] = __("At least 200 characters in the description");
                }
                if (!$this->getKeywords()) {
                    $errors[] = __("The keywords");
                }
                if (!$this->getMainCategoryId()) {
                    $errors[] = __("The main category");
                }
            }

            return $errors;
        }
    }

    /**
     * @param $checkSourcesAccessType
     * @return array
     */
    public function isAvailableForPublishingUnified($checkSourcesAccessType)
    {
        $errors = [];
        if ($this->getPages()->count() < 1) {
            $errors[] = __("At least, 1 page in the application");
        }

        if (!$this->getData('background_image_unified')) {
            $errors[] = __("The homepage image");
        }

        if (!$this->getData('startup_image_unified')) {
            $errors[] = __("The splashscreen image");
        }

        if (!$this->getData('icon')) {
            $errors[] = __("The desktop icon");
        }

        if (!$this->getName()) {
            $errors[] = __("The application name");
        }

        if ($checkSourcesAccessType) {
            if (!$this->getBundleId()) {
                $errors[] = __("The bundle id");
            }
        } else {
            if (!$this->getDescription()) {
                $errors[] = __("The description");
            } else if (strlen($this->getDescription()) < 200) {
                $errors[] = __("At least 200 characters in the description");
            }
            if (!$this->getKeywords()) {
                $errors[] = __("The keywords");
            }
            if (!$this->getMainCategoryId()) {
                $errors[] = __("The main category");
            }
        }

        return $errors;
    }

    /**
     * @return bool
     * Previously isFreeTrialExpired
     */
    public function canAccessEditor()
    {
        return true;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getBackgroundImageUrl($type = 'normal')
    {

        try {
            $backgroundImage = '';
            if ($background_image = $this->getData('background_image')) {
                if ($type === 'normal') {
                    $background_image .= '.jpg';
                } else if ($type === 'retina') {
                    $background_image .= '@2x.jpg';
                } else if ($type === 'retina4') {
                    $background_image .= '-568h@2x.jpg';
                }

                if (file_exists(self::getBaseImagePath() . $background_image)) {
                    $backgroundImage = self::getImagePath() . $background_image;
                }
            }
        } catch (Exception $e) {
            $backgroundImage = '';
        }

        if (empty($backgroundImage)) {
            $backgroundImage = $this->getNoBackgroundImageUrl($type);
        }

        return $backgroundImage;
    }

    /**
     * @param string $type
     * @param bool $return
     * @return string
     */
    public function getHomepageBackgroundImageUrl($type = '', $return = false)
    {
        if ($this->getSplashVersion() == 2) {
            return $this->getHomepageBackgroundUnified();
        }

        try {

            $image = '';

            switch ($type) {
                case "landscape_hd":
                    $image_name = $this->getData('background_image_landscape_hd');
                    break;
                case "landscape_tablet":
                    $image_name = $this->getData('background_image_landscape_tablet');
                    break;
                case "landscape_standard":
                case "landscape":
                    $image_name = $this->getData('background_image_landscape');
                    break;
                case "hd":
                    $image_name = $this->getData('background_image_hd');
                    break;
                case "tablet":
                    $image_name = $this->getData('background_image_tablet');
                    break;
                case "unified":
                    $image_name = $this->getData('background_image_unified');
                    break;
                case "standard":
                default:
                    $image_name = $this->getData('background_image');
                    break;
            }

            if ($return === true) {
                return $image_name;
            }

            // In case we don't have landscape ones for example!!
            if (empty($image_name)) {
                $image_name = $this->getData('background_image');
            }

            if (!empty($image_name)) {
                if (file_exists(self::getBaseImagePath() . $image_name)) {
                    $image = self::getImagePath() . $image_name;
                } else if (file_exists(self::getBaseTemplatePath() . $image_name)) {
                    $image = self::getTemplatePath() . $image_name;
                }
            }

        } catch (Exception $e) {
            $image = '';
        }

        if (empty($image)) {
            $image = $this->getNoBackgroundImageUrl($type);
        }

        return $image;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getHomepageBackgroundUnified()
    {
        try {
            $imageName = $this->getData('background_image_unified');
            if (is_file(Core_Model_Directory::getBasePathTo($imageName))) {
                return $imageName;
            }
            if (is_file(Core_Model_Directory::getBasePathTo(self::PATH_IMAGE . $imageName))) {
                return self::PATH_IMAGE . $imageName;
            }
        } catch (\Exception $e) {
            //
        }
        return '';
    }

    /**
     * @return array|mixed|null|string
     */
    public function getStartupBackgroundUnified()
    {
        try {
            $imageName = $this->getData('startup_image_unified');
            if (is_file(Core_Model_Directory::getBasePathTo($imageName))) {
                return $imageName;
            }
            if (is_file(Core_Model_Directory::getBasePathTo(self::PATH_IMAGE . $imageName))) {
                return self::PATH_IMAGE . $imageName;
            }
        } catch (\Exception $e) {
            //
        }
        return '';
    }

    /**
     * @return array
     */
    public function getSliderImages()
    {

        try {

            $library = new Media_Model_Library();
            $images = $library->find($this->getApplication()->getHomepageSliderLibraryId())->getImages();

        } catch (Exception $e) {
            $images = [];
        }

        return $images;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getNoBackgroundImageUrl($type = 'standard')
    {
        switch ($type) {
            case 'hd':
                $imageName = 'no-background-hd.jpg';
                break;
            case 'tablet':
                $imageName = 'no-background-tablet.jpg';
                break;
            case 'unified':
                $imageName = 'no-background-unified.jpg';
                break;
            case 'standard':
            default:
                $imageName = 'no-background.jpg';
                break;
        }

        return self::getImagePath() . '/placeholder/' . $imageName;
    }

    /**
     * @param string $url
     * @param array $params
     * @param null $locale
     * @param bool $forceKey
     * @return array|mixed|string
     */
    public function getUrl($url = '', array $params = [], $locale = null, $forceKey = false)
    {

        $is_ionic_url = false;
        if (!empty($params["use_ionic"])) {
            $is_ionic_url = true;
            unset($params["use_ionic"]);
        }

        if (!$this->getDomain()) $forceKey = true;

        if ($is_ionic_url) {
            $url = Core_Model_Url::create($url, $params, $locale);
        } else if ($forceKey) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $use_key = $request->useApplicationKey();
            $request->useApplicationKey(true);
            $url = Core_Model_Url::create($url, $params, $locale);
            $request->useApplicationKey($use_key);
        } else {
            $domain = rtrim($this->getDomain(), "/") . "/";
            $protocol = "https://";
            $url = Core_Model_Url::createCustom($protocol . $domain, $url, $params, $locale);
        }

        if (substr($url, strlen($url) - 1, 1) != "/") {
            $url .= "/";
        }

        return $url;

    }

    /**
     * @param string $url
     * @param array $params
     * @param null $locale
     * @param bool $forceKey
     * @return array|mixed|string
     */
    public function getIonicUrl($url = '', array $params = [], $locale = null, $forceKey = false)
    {
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

    /**
     * @param string $uri
     * @param array $params
     * @param null $locale
     * @return array|mixed|string
     */
    public function getPath($uri = '', array $params = [], $locale = null)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $useKey = (bool)$request->useApplicationKey();
        $request->useApplicationKey(true);
        if ($this->getValueId()) {
            $param["value_id"] = $this->getValueId();
        }
        $url = parent::getPath($uri, $params, $locale);
        $request->useApplicationKey($useKey);

        return $url;

    }

    /**
     * @return string
     */
    public static function getIonicPath()
    {
        return trim(Core_Model_Directory::getPathTo(self::PATH_TO_SOURCE_CODE), "/");
    }

    /**
     * @return array|mixed|null|string
     */
    public function requireToBeLoggedIn()
    {
        return $this->getData('require_to_be_logged_in');
    }

    /**
     * @return $this
     * @throws Siberian_Exception
     */
    public function duplicate()
    {

        // Retrieve all the accounts
        $admins = $this->getAdmins();
        $admin_ids = [];
        foreach ($admins as $admin) {
            $admin_ids[] = $admin;
        }

        // Duplicate the design blocks
        $blocks = [];
        foreach ($this->getBlocks() as $block) {
            $blocks[] = $block->getData();
        }
        $layout_id = $this->getLayoutId();

        // Load the options
        $option_values = $this->getOptions();
        $value_ids = [];

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
            ->save();

        // Duplicate the images folder
        $old_app_folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath() . DIRECTORY_SEPARATOR . $old_app_id);
        $target_app_folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::getImagePath() . DIRECTORY_SEPARATOR . $this->getId());
        Core_Model_Directory::duplicate($old_app_folder, $target_app_folder);

        // Save the design
        if (!empty($blocks)) {
            foreach ($blocks as $template_block) {
                $block = new Template_Model_Block();
                $block->setData($template_block);
                $block->setAppId($this->getId());
                $block->save();
            }
        }
        $this->setLayoutId($layout_id)
            ->save();

        // Copy all the features but folders
        foreach ($option_values as $option_value) {
            if (!in_array($option_value->getCode(), ['folder', 'folder_v2'])) {
                $option_value->copyTo($this);
                $value_ids[$option_value->getOldValueId()] = $option_value->getId();
            }
        }

        // @deprecated, disabled until it's fixed/replaced with new duplication system.
        // Copy the folders
        //foreach($option_values as $option_value) {
        //    if($option_value->getCode() == 'folder') {
        //        $option_value->copyTo($this);
        //        $value_ids[$option_value->getOldValueId()] = $option_value->getId();
        //    }
        //}

        // Lock the features
        $locker = new Padlock_Model_Padlock();
        $old_locked_value_ids = $locker->getValueIds($old_app_id);
        $locked_value_ids = [];
        foreach ($old_locked_value_ids as $old_locked_value_id) {
            if (!empty($value_ids[$old_locked_value_id])) {
                $locked_value_ids[] = $value_ids[$old_locked_value_id];
            }
        }

        if (!empty($locked_value_ids)) {
            $locker
                ->setValueIds($locked_value_ids)
                ->saveValueIds($this->getId());
        }

        // Set the accounts to the application
        $this->setAdminIds($admin_ids);
        $this->save();

        //copy slideshow if needed
        if ($this->getHomepageSliderIsVisible()) {
            $app_id = $this->getId();
            //create new lib
            $library = new Media_Model_Library();
            $library->setName("homepage_slider_" . $app_id)
                ->save();
            $library_id = $library->getId();

            //duplicate current images
            $library_image = new Media_Model_Library_Image();
            $images = $library_image->findAll(
                ["library_id" => $this->getHomepageSliderLibraryId()]
            );
            foreach ($images as $image) {
                $oldLink = $image->getLink();
                $explodedLink = explode("/", $oldLink);
                $explodedLink[3] = $app_id;

                $newLink = implode_polyfill("/", $explodedLink);

                //copy file
                mkdir(dirname(getcwd() . $newLink), 0777, true);
                copy(getcwd() . $oldLink, getcwd() . $newLink);

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

    /**
     * @var null
     */
    public static $singleton = null;

    /**
     * @param $application
     */
    public static function setSingleton($application)
    {
        self::$singleton = $application;
    }

    /**
     * @return null
     */
    public static function getSingleton()
    {
        return self::$singleton;
    }

    /**
     * @param bool $base64
     * @return string
     */
    public function _getImage($name)
    {
        return $this->__getBase64Image($this->getData($name));
    }

    /**
     * @param $base64
     * @param $option
     * @return $this
     */
    public function _setImage($name, $base64, $option, $width = 512, $height = 512)
    {
        $path = $this->__setImageFromBase64($base64, $option, $width, $height);
        $this->setData($name, $path);

        return $this;
    }

    /**
     * Convert application into YAML with base64 images
     *
     * @return string
     */
    public function toYml()
    {
        $data = $this->getData();

        $data["background_image"] = $this->_getImage("background_image");
        $data["background_image_hd"] = $this->_getImage("background_image_hd");
        $data["background_image_tablet"] = $this->_getImage("background_image_tablet");
        $data["icon"] = $this->_getImage("icon");
        $data["startup_image"] = $this->_getImage("startup_image");
        $data["startup_image_retina"] = $this->_getImage("startup_image_retina");
        $data["startup_image_iphone_6"] = $this->_getImage("startup_image_iphone_6");
        $data["startup_image_iphone_6_plus"] = $this->_getImage("startup_image_iphone_6_plus");
        $data["startup_image_ipad_retina"] = $this->_getImage("startup_image_ipad_retina");

        $data["created_at"] = null;
        $data["updated_at"] = null;

        $data["name"] = null;
        $data["bundle_id"] = null;
        $data["key"] = null;

        /** Colors */
        $template_block_app_model = new Template_Model_Block_App();
        $tbas = $template_block_app_model->findAll([
            "app_id = ?" => $this->getId(),
        ]);

        $dataset_tbas = [];
        foreach ($tbas as $tba) {
            $tba_data = $tba->getData();
            $tba_data["created_at"] = null;
            $tba_data["updated_at"] = null;

            $dataset_tbas[] = $tba_data;
        }

        $dataset = [
            "application" => $data,
            "colors" => $dataset_tbas,
        ];

        $dataset = Siberian_Yaml::encode($dataset);

        return $dataset;
    }

    /**
     * This will return the Privacy Policy GDPR with fullfilled placeholders.
     *
     * @return array|mixed|null|string
     */
    public function getPrivacyPolicyGdpr()
    {
        $whitelabel = Siberian::getWhitelabel();
        if (Siberian::getWhitelabel()) {
            $companyName = $whitelabel->getCompany();
            $contactFull = $whitelabel->getCompany() . "\n" .
                $whitelabel->getAddress() . "\n" .
                $whitelabel->getPhone() . "\n" .
                __('Contact e-mail') . ': ' . $whitelabel->getEmail();
            $platformName = empty($whitelabel->getName()) ? $whitelabel->getHost() : $whitelabel->getName();
        } else {
            $companyName = __get('company_name');
            $contactFull = __get('company_name') . "\n" .
                __get('company_address') . "\n" .
                __get('company_country') . "\n" .
                __get('company_phone') . "\n" .
                __('Contact e-mail') . ': ' . __get('support_email');
            $platformName = __get('platform_name');
        }

        $privacyPolicyGdpr = str_replace(
            [
                '#APP_NAME',
                '#COMPANY.NAME#',
                '#PLATFORM.NAME#',
                '#CONTACT.FULL#',
            ],
            [
                '<b>' . $this->getName() . '</b>',
                '<b>' . $companyName . '</b>',
                '<b>' . $platformName . '</b>',
                '<b>' . nl2br($contactFull) . '</b>'
            ],
            $this->getData('privacy_policy_gdpr')
        );

        return $privacyPolicyGdpr;
    }

    /**
     * This action will completely wipe the Application & all it's content & resources!
     */
    public function wipe()
    {
        $appId = $this->getId();

        // 1. Pre-check PE!
        if (Siberian_Version::is('PE')) {
            $salesInvoices = (new Sales_Model_Invoice())
                ->findAllv2([
                    [
                        'filter' => 'si.app_id = ?',
                        'value' => $appId,
                    ]
                ]);

            if ($salesInvoices->count() > 0) {
                throw new Siberian_Exception(__('This Application has some invoices associated, you can not delete it!'));
            }
        }

        // 1.1. Check for any M-Commerce invoices
        $mcommerceOption = (new Application_Model_Option())
            ->find([
                'code' => 'm_commerce'
            ]);
        if ($mcommerceOption->getId()) {
            $optionValue = (new Application_Model_Option_Value())
                ->find([
                    'option_id' => $mcommerceOption->getId(),
                    'app_id' => $appId
                ]);
            if ($optionValue->getId()) {
                $mcommerce = (new Mcommerce_Model_Mcommerce())
                    ->find([
                        'value_id' => $optionValue->getId()
                    ]);
                if ($mcommerce->getId()) {
                    $mcommerceOrder = (new Mcommerce_Model_Order())
                        ->findAll([
                            'mcommerce_id = ?' => $mcommerce->getId()
                        ]);
                    if ($mcommerceOrder->count() > 0) {
                        throw new Siberian_Exception(__('This Application has some M-Commerce invoices associated, you can not delete it!'));
                    }
                }
            }
        }

        // 2. Find the resources folder.
        if (strlen($appId) <= 0) {
            throw new Siberian_Exception(__('Seems the appId is empty or invalid, aborting!'));
        }

        $pathToImages = Core_Model_Directory::getBasePathTo('/images/application/' . $appId . '/');
        if (strrpos($pathToImages, '//') === 0) {
            throw new Siberian_Exception(__('Seems the computed path could delete unwanted content, aborting!<br />' . $pathToImages));
        }

        $absolutePath = realpath($pathToImages);
        $applicationAbsolutePath = realpath(Core_Model_Directory::getBasePathTo('/images/application'));
        if ($absolutePath === $applicationAbsolutePath) {
            throw new Siberian_Exception(__('Seems we could delete unwanted files, aborting!'));
        }

        if (is_dir($pathToImages)) {
            // 2.1. Secure any white space
            $pathToImagesSafe = preg_replace("#\s+#i", '', $pathToImages);

            if (!empty($pathToImagesSafe)) {
                // 2.2. Then the folder itself
                exec('rm -rf "' . $pathToImagesSafe . '"');
            }
        }

        // 3. Delete all related media images (we do it first manually to ensure it's clean because there is no cascade here)
        $mediaLibraryImages = (new Media_Model_Library_Image())
            ->findAll([
                'app_id = ?' => $appId
            ]);
        foreach ($mediaLibraryImages as $mediaLibraryImage) {
            $path = Core_Model_Directory::getBasePathTo($mediaLibraryImage->getData('link'));
            if (is_file($path)) {
                unlink($path);
            }
            $mediaLibraryImage->delete();
        }

        // 4. Delete customers
        $customers = (new Customer_Model_Customer())
            ->findAll([
                'app_id = ?' => $appId
            ]);

        foreach ($customers as $customer) {
            $image = $customer->getData('image');
            if (!empty($image)) {
                $imagePath = Core_Model_Directory::getBasePathTo('/images/customer' . $image);
                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }
            $customer->delete();
        }

        // 5. Delete the Application itself
        $this->delete();
    }

    /**
     * Watch application disk size
     *
     * @param Siberian_Cron $cron
     * @param Cron_Model_Cron $task
     */
    public static function getSizeOnDisk($cron, $task)
    {
        // We do really need to lock this thing!
        $cron->lock($task->getId());

        try {
            $db = Zend_Db_Table::getDefaultAdapter();
            $appIds = $db->fetchAssoc('SELECT app_id FROM application;');

            foreach ($appIds as $appId) {
                $appId = $appId['app_id'];
                $assetsDirectory = Core_Model_Directory::getBasePathTo('/images/application/' . $appId . '/');
                if (!empty($appId) && is_dir($assetsDirectory)) {
                    try {
                        $assetsSize = dirSize($assetsDirectory);
                    } catch (Exception $e) {
                        $assetsSize = 0;
                    }

                    $db->query('UPDATE application SET size_on_disk = ' . $assetsSize .
                        ' WHERE app_id = ' . $appId . ';');
                }
                usleep(10);
            }
        } catch (Exception $e) {
            $cron->log($e->getMessage());
            $task->saveLastError($e->getMessage());
        }

        // Releasing!
        $cron->unlock($task->getId());
    }

    /**
     * @throws Zend_Currency_Exception
     */
    public function checkForUpgrades()
    {
        // 4.17.9 Stripe & Currency upgrade
        if (version_compare($this->getVersion(), "4.17.9", "<")) {
            try {
                // Hijacking locale to currency, to local user format!
                $tmpCurrency = new Zend_Currency(null, new Zend_Locale($this->getLocale()));
                $currencyOrLocale = $tmpCurrency->getShortName();

                if ($currencyOrLocale !== null) {
                    $newCurrency = new Zend_Currency($currencyOrLocale, new Zend_Locale($language));
                } else {
                    $newCurrency = Core_Model_Language::getCurrentCurrency();
                }
            } catch (Exception $e) {
                // We need at least to default to something to display!
                $newCurrency = new Zend_Currency();
            }

            $currency = $newCurrency->getShortName();

            $this
                ->setCurrency(strtoupper($currency))
                ->setVersion("4.17.9")
                ->save();
        }
    }
}
