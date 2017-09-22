<?php

abstract class Core_View_Default_Abstract extends Siberian_View
{

    protected static $_application;
    protected static $_acl;
    protected static $_session = array();
    protected static $_device;
    protected static $_blocks;

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function isProduction() {
        return APPLICATION_ENV == 'production';
    }

    public function isSae() {
        return Siberian_Version::TYPE == "SAE";
    }
    
    public function isMae() {
        return Siberian_Version::TYPE == "MAE";
    }

    public function isPe() {
        return Siberian_Version::TYPE == "PE";
    }

    public function getSession($type = null) {

        if(is_null($type)) $type = SESSION_TYPE;

        if(isset(self::$_session[$type])) {
            return self::$_session[$type];
        } else {
            $session = new Core_Model_Session($type);
            self::setSession($session, $type);
            return $session;
        }
    }

    public static function setSession($session, $type = 'front') {
        self::$_session[$type] = $session;
    }

    public function getApplication() {
        return self::$_application;
    }

    public static function setApplication($application) {
        self::$_application = $application;
    }

    public function _getAcl() {
        return self::$_acl;
    }

    public static function setAcl($acl) {
        self::$_acl = $acl;
    }

    protected function _canAccess($resource, $value_id = null) {
        return self::_getAcl() ? self::_getAcl()->isAllowed($resource, $value_id) : true;
    }

    protected function _canAccessAnyOf($resources, $value_id = null) {
        foreach($resources as $resource) {
            $allowed = self::_canAccess($resource, $value_id);
            if($allowed)
                return true;
        }
    }

    public function getDevice() {
        return self::$_device;
    }

    public static function setDevice($device) {
        self::$_device = $device;
    }

    public static function setBlocks($blocks) {
        self::$_blocks = $blocks;
    }

    public function getBlocks() {
        return self::$_blocks;
    }

    public function getBlock($code) {

        foreach($this->getBlocks() as $block) {
            if($block->getCode() == $code) return $block;
        }

        return new Template_Model_Block();

    }

    public function _($text) {
        $args = func_get_args();
        return Core_Model_Translator::translate($text, $args);
    }

    public function isHomePage() {
        return $this->getRequest()->getParam('module') == 'Front' &&
            $this->getRequest()->getParam('controller') == 'index' &&
            $this->getRequest()->getParam('action') == 'index'
        ;
    }

    public function isMobileDevice() {
        return DEVICE_TYPE == 'mobile';
    }

    /** @migration
     * @todo use good path .... */
    public function getJs($name) {
        //Zend_Debug::dump($this->getRequest()->getMediaUrl());
        //Zend_Debug::dump('/app/sae/design/' . APPLICATION_TYPE . '/' . DESIGN_CODE . '/js/' . $name);
        die("@migration, Core_View_Default_Abstract");

        return $this->getRequest()->getMediaUrl().'/app/sae/design/' . APPLICATION_TYPE . '/' . DESIGN_CODE . '/js/' . $name;
    }

    public function getImagePath() {
        return Core_Model_Directory::getDesignPath(false) . '/images';
    }
    public function getBaseImagePath() {
        return Core_Model_Directory::getDesignPath(true) . '/images';
    }

    public function getImage($name, $base = false) {

        $path = Siberian_Cache_Design::getBasePath("/images/".$name);
        if(file_exists($path)) {
            return Siberian_Cache_Design::getPath("/images/".$name);

        } else if(file_exists($this->getBaseImagePath() . "/" . $name)) {
            $path = $base ? $this->getBaseImagePath() : $this->getImagePath();
            return $path."/".$name;

        } else if(file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);

        }

        return "";

    }

    public function getColorizedImage($image_id, $color) {

        # Not usable with colorized images
        Siberian_Media::disableTemporary();

        $color = str_replace('#', '', $color);
        $id = md5(implode('+', array($image_id, $color)));
        $url = '';

        $image = new Media_Model_Library_Image();
        if(is_numeric($image_id)) {
            $image->find($image_id);
            if(!$image->getId()) {
                return $url;
            }
            if(!$image->getCanBeColorized()) {
                $color = null;
            }
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path, $image->getAppId());
        } else if(!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
            if(preg_match("#/customer_account/#", $image_id)) {
                $icon_id = $this->getApplication()->getAccountIconId();
                $library = new Media_Model_Library_Image();
                $icon = $library->find($icon_id);
                if(!$icon->getCanBeColorized()) {
                    $color = null;
                }
            }
            if(preg_match("#/more_items/#", $image_id)) {
                $icon_id = $this->getApplication()->getMoreIconId();
                $library = new Media_Model_Library_Image();
                $icon = $library->find($icon_id);
                if(!$icon->getCanBeColorized()) {
                    $color = null;
                }
            }
            $path = Core_Model_Directory::getBasePathTo($image_id);
        } else {
            $path = $image_id;
        }

        try {
            $image = new Core_Model_Lib_Image();
            $image->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize()
            ;
            $url = $image->getUrl();
        } catch(Exception $e) {
            $url = '';
        }

        return $url;
    }

    public function getExternalUrl($path) {
        if(!empty($path) AND substr($path, 0, 1) != "/") $path = "/".$path;
        return Core_Model_Url::create()."external".$path;
    }

    public function getUrl($url = '', array $params = array(), $locale = null) {
        return Core_Model_Url::create($url, $params, $locale);
    }

    public function getPath($uri = '', array $params = array(), $locale = null) {
        return Core_Model_Url::createPath($uri, $params);
    }

    public function getBasePath($remove_app = false) {

        $path_info = array_filter(explode("/", $this->getRequest()->getPathInfo()));
        $request_uri = array_filter(explode("/", $this->getRequest()->getRequestUri()));

        if($remove_app AND $this->getRequest()->isApplication() AND $this->getRequest()->useApplicationKey()) {
            if(Siberian_Version::is("SAE")) {
                $path_info = array_diff($path_info, array(Application_Model_Application::OVERVIEW_PATH));
                $request_uri = array_diff($request_uri, array(Application_Model_Application::OVERVIEW_PATH));
            } else {
                $path_info = array_diff($path_info, array($this->getRequest()->getApplicationKey()));
                $request_uri = array_diff($request_uri, array($this->getRequest()->getApplicationKey()));
            }

        }

        $base_path = array_diff($request_uri, $path_info);
        $base_path = join("/", $base_path);

        if(!empty($base_path) AND stripos($base_path, "/") !== 0) $base_path = "/$base_path";

        return $base_path;

    }

    public function getCurrentUrl($withParams = true, $locale = null) {
        return Core_Model_Url::current($withParams, $locale);
    }

    protected function _renderZendMenu($xml) {
        $config = new Zend_Config_Xml($xml);
        $this->navigation(new Zend_Navigation($config));
        if(!$this->getPluginLoader('helper')->getPaths(Zend_View_Helper_Navigation::NS)) {
            $this->addHelperPath('Zend/View/Helper/Navigation', 'Zend_View_Helper_Navigation');
        }

        if(!$this->getPluginLoader('helper')->getPaths('Siberian_View_Helper_Navigation')) {
            $this->addHelperPath('Siberian/View/Helper/Navigation', 'Siberian_View_Helper_Navigation');
        }

        $nav = $this->navigation();
        return $nav->menu();
    }


    /**
     * Custom background for features
     *
     * @param $option
     * @param $toggleDown
     * @param $marginTop
     * @return mixed
     */
    public function importBackground($option_value, $toggleDown = true, $marginTop = true) {

        $background_section = $this->getLayout()
            ->addPartial('background_image', 'Core_View_Default', 'application/customization/features/edit/background_image-2.phtml')
            ->setOptionValue($option_value)
            ->setValueId($option_value->getId())
            ->setToggleDown($toggleDown)
            ->setMarginTop($marginTop)
            ->toHtml();

        return $background_section;
    }

    /**
     * Custom layouts for features
     *
     * @param $option
     * @param $toggleDown
     * @return mixed
     */
    public function importLayout($option_value, $toggleDown = true) {

        // In case there is only 1 layout, returns an HTML comment, but nothing more!
        $layouts = $option_value->getLayouts();
        if ($layouts->count() < 1) {
            return '<!-- importLayout:nothing to load -->';
        }

        $layout_section = $this->getLayout()
            ->addPartial('background_image', 'Core_View_Default', 'application/customization/features/edit/layout-2.phtml')
            ->setOptionValue($option_value)
            ->setValueId($option_value->getId())
            ->setToggleDown($toggleDown)
            ->toHtml();

        return $layout_section;
    }

    public function getLogo() {

        try {

            $logo = "";
            if($this->getCurrentWhiteLabelEditor()) {
                $logo = $this->getCurrentWhiteLabelEditor()->getLogoUrl();
            }

            if(!$logo) {
                $logo = System_Model_Config::getValueFor("logo");
            }

            if(!$logo) {
                $logo = $this->getImage("header/logo.png");
            }

        } catch(Exception $e) {
            $logo = $this->getImage("header/logo.png");
        }

        return $logo;

    }
}
