<?php

use Siberian\Version;
use Siberian\Image;

/**
 * Class Core_View_Default_Abstract
 */
abstract class Core_View_Default_Abstract extends Siberian\View
{

    /**
     * @var
     */
    protected static $_application;

    /**
     * @var
     */
    protected static $_acl;

    /**
     * @var array
     */
    protected static $_session = [];

    /**
     * @var
     */
    protected static $_device;

    /**
     * @var
     */
    protected static $_blocks;


    /**
     * Core_View_Default_Abstract constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function isProduction()
    {
        return APPLICATION_ENV == 'production';
    }

    /**
     * @return bool
     */
    public function isSae()
    {
        return Siberian_Version::TYPE == "SAE";
    }

    /**
     * @return bool
     */
    public function isMae()
    {
        return Siberian_Version::TYPE == "MAE";
    }

    /**
     * @return bool
     */
    public function isPe()
    {
        return Siberian_Version::TYPE == "PE";
    }

    /**
     * @param null $type
     * @return Core_Model_Session|mixed
     * @throws Zend_Session_Exception
     */
    public function getSession($type = null)
    {

        if (is_null($type)) $type = SESSION_TYPE;

        if (isset(self::$_session[$type])) {
            return self::$_session[$type];
        } else {
            $session = new Core_Model_Session($type);
            self::setSession($session, $type);
            return $session;
        }
    }

    /**
     * @param $session
     * @param string $type
     */
    public static function setSession($session, $type = 'front')
    {
        self::$_session[$type] = $session;
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return self::$_application;
    }

    /**
     * @param $application
     */
    public static function setApplication($application)
    {
        self::$_application = $application;
    }

    /**
     * @return Acl_Model_Acl
     */
    public function _getAcl()
    {
        return self::$_acl;
    }

    /**
     * @return mixed
     */
    public static function _sGetAcl()
    {
        return self::$_acl;
    }

    /**
     * @param $acl
     */
    public static function setAcl($acl)
    {
        self::$_acl = $acl;
    }

    /**
     * @param $resource
     * @param null $value_id
     * @return bool
     */
    protected function _canAccess($resource, $value_id = null)
    {
        return self::_sGetAcl() ? self::_sGetAcl()->isAllowed($resource, $value_id) : true;
    }

    /**
     * @param $resource
     * @param null $value_id
     * @return bool
     */
    protected static function _sCanAccess($resource, $value_id = null)
    {
        return self::_sGetAcl() ? self::_sGetAcl()->isAllowed($resource, $value_id) : true;
    }

    /**
     * @param $resources
     * @param null $value_id
     * @return bool
     */
    protected function _canAccessAnyOf($resources, $value_id = null)
    {
        foreach ($resources as $resource) {
            $allowed = self::_sCanAccess($resource, $value_id);
            if ($allowed) {
                return true;
            }

        }
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return self::$_device;
    }

    /**
     * @param $device
     */
    public static function setDevice($device)
    {
        self::$_device = $device;
    }

    /**
     * @param $blocks
     */
    public static function setBlocks($blocks)
    {
        self::$_blocks = $blocks;
    }

    /**
     * @return mixed
     */
    public function getBlocks()
    {
        return self::$_blocks;
    }

    /**
     * @param $code
     * @return Template_Model_Block
     */
    public function getBlock($code)
    {
        foreach ($this->getBlocks() as $block) {
            if ($block->getCode() == $code) {
                return $block;
            }
        }

        return new Template_Model_Block();
    }

    /**
     * @param $code
     * @return Template_Model_Block
     */
    public static function sGetBlock($code)
    {
        foreach (self::$_blocks as $block) {
            if ($block->getCode() === $code) {
                return $block;
            }
        }

        return new Template_Model_Block();
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public function _($text)
    {
        return call_user_func_array("__", func_get_args());
    }

    /**
     * @return bool
     */
    public function isHomePage()
    {
        return $this->getRequest()->getParam('module') == 'Front' &&
            $this->getRequest()->getParam('controller') == 'index' &&
            $this->getRequest()->getParam('action') == 'index';
    }

    /**
     * @return bool
     */
    public function isMobileDevice()
    {
        return DEVICE_TYPE == 'mobile';
    }

    /**
     * @deprecated
     *
     * @param $name
     * @return string
     */
    public function getJs($name)
    {
        die("@migration, Core_View_Default_Abstract");

        return $this->getRequest()->getMediaUrl() . '/app/sae/design/' . APPLICATION_TYPE . '/' . DESIGN_CODE . '/js/' . $name;
    }

    /**
     * @return string
     * @throws Zend_Exception
     */
    public function getImagePath()
    {
        return Core_Model_Directory::getDesignPath(false) . '/images';
    }

    /**
     * @return string
     * @throws Zend_Exception
     */
    public function getBaseImagePath()
    {
        return Core_Model_Directory::getDesignPath(true) . '/images';
    }

    /**
     * @param $name
     * @param bool $base
     * @return bool|string
     * @throws Zend_Exception
     */
    public function getImage($name, $base = false)
    {
        $path = Siberian_Cache_Design::getBasePath("/images/" . $name);
        if (file_exists($path)) {
            return Siberian_Cache_Design::getPath("/images/" . $name);
        } else if (file_exists($this->getBaseImagePath() . "/" . $name)) {
            $path = $base ? $this->getBaseImagePath() : $this->getImagePath();
            return $path . "/" . $name;
        } else if (file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);
        }

        return "";
    }

    /**
     * @param $image_id
     * @param $color
     * @return string
     */
    public function getColorizedImage($image_id, $color)
    {

        # Not usable with colorized images
        Siberian_Media::disableTemporary();

        $color = str_replace('#', '', $color);
        $id = md5(implode_polyfill('+', [$image_id, $color]));
        $url = '';

        $image = new Media_Model_Library_Image();
        if (is_numeric($image_id)) {
            $image->find($image_id);
            if (!$image->getId()) {
                return $url;
            }
            if (!$image->getCanBeColorized()) {
                $color = null;
            }
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path, $image->getAppId());
        } else if (!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
            if (preg_match("#/customer_account/#", $image_id)) {
                $icon_id = $this->getApplication()->getAccountIconId();
                $library = new Media_Model_Library_Image();
                $icon = $library->find($icon_id);
                if (!$icon->getCanBeColorized()) {
                    $color = null;
                }
            }
            if (preg_match("#/more_items/#", $image_id)) {
                $icon_id = $this->getApplication()->getMoreIconId();
                $library = new Media_Model_Library_Image();
                $icon = $library->find($icon_id);
                if (!$icon->getCanBeColorized()) {
                    $color = null;
                }
            }
            $path = Core_Model_Directory::getBasePathTo($image_id);
        } else {
            $path = $image_id;
        }

        try {
            $image = new Core_Model_Lib_Image();
            $image
                ->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize();
            $url = $image->getUrl();
        } catch (Exception $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * @param $path
     * @return string
     */
    public function getExternalUrl($path)
    {
        if (!empty($path) AND substr($path, 0, 1) != "/") $path = "/" . $path;
        return Core_Model_Url::create() . "external" . $path;
    }

    /**
     * @param string $url
     * @param array $params
     * @param null $locale
     * @return array|mixed|string
     */
    public function getUrl($url = '', array $params = [], $locale = null)
    {
        return Core_Model_Url::create($url, $params, $locale);
    }

    /**
     * @param string $uri
     * @param array $params
     * @param null $locale
     * @return array|mixed|string
     */
    public function getPath($uri = '', array $params = [], $locale = null)
    {
        return Core_Model_Url::createPath($uri, $params);
    }

    /**
     * @param bool $remove_app
     * @return array|string
     */
    public function getBasePath($remove_app = false)
    {

        $path_info = array_filter(explode("/", $this->getRequest()->getPathInfo()));
        $request_uri = array_filter(explode("/", $this->getRequest()->getRequestUri()));

        if ($remove_app AND $this->getRequest()->isApplication() AND $this->getRequest()->useApplicationKey()) {
            if (Siberian_Version::is("SAE")) {
                $path_info = array_diff($path_info, [Application_Model_Application::OVERVIEW_PATH]);
                $request_uri = array_diff($request_uri, [Application_Model_Application::OVERVIEW_PATH]);
            } else {
                $path_info = array_diff($path_info, [$this->getRequest()->getApplicationKey()]);
                $request_uri = array_diff($request_uri, [$this->getRequest()->getApplicationKey()]);
            }

        }

        $base_path = array_diff($request_uri, $path_info);
        $base_path = join("/", $base_path);

        if (!empty($base_path) AND stripos($base_path, "/") !== 0) $base_path = "/$base_path";

        return $base_path;

    }

    /**
     * @param bool $withParams
     * @param null $locale
     * @return array|mixed|string
     */
    public function getCurrentUrl($withParams = true, $locale = null)
    {
        return Core_Model_Url::current($withParams, $locale);
    }

    /**
     * @param $xml
     * @return mixed
     * @throws Zend_Config_Exception
     * @throws Zend_Navigation_Exception
     * @throws Zend_View_Exception
     */
    protected function _renderZendMenu($xml)
    {
        $config = new Zend_Config_Xml($xml);
        $this->navigation(new Zend_Navigation($config));
        if (!$this->getPluginLoader('helper')->getPaths(Zend_View_Helper_Navigation::NS)) {
            $this->addHelperPath('Zend/View/Helper/Navigation', 'Zend_View_Helper_Navigation');
        }

        if (!$this->getPluginLoader('helper')->getPaths('Siberian_View_Helper_Navigation')) {
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
    public function importBackground($option_value, $toggleDown = true, $marginTop = true)
    {

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
    public function importLayout($option_value, $toggleDown = true)
    {

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

    /**
     * @var array
     */
    static public $lastIconInfos = [];

    /**
     * @return array
     */
    protected function getLastIconInfos ()
    {
        return static::$lastIconInfos;
    }

    /**
     * @var string
     */
    protected $_icon_color;

    /**
     * @param $option
     * @param null $enforcedColor
     * @param bool $forceColorizable
     * @return string
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    protected function getIconUrl($option, $enforcedColor = null, $forceColorizable = false): string
    {
        // Enforces a color (but not the colorization*)
        if ($enforcedColor !== null && empty($this->_icon_color)) {
            $this->_icon_color = $enforcedColor;
        }

        $image = (new Media_Model_Library_Image());

        if ($option->getOptionId() === 'customer_account') {
            if ($this->getApplication()->getAccountIconId()) {
                $image->find($this->getApplication()->getAccountIconId());
            } else {
                $image->find($option->getDefaultIconId());
            }
        } else if ($option->getOptionId() === 'more_items') {
            if ($this->getApplication()->getMoreIconId()) {
                $image->find($this->getApplication()->getMoreIconId());
            } else {
                $image
                    ->setLink('/tabbar/more_items-flat.png')
                    ->setCanBeColorized(true);
            }
        } else {
            if ($option->getIconId()) {
                $image->find($option->getIconId());
            } else {
                $image->find($option->getDefaultIconId());
            }
        }

        if (!$image->checkFile()) {
            // Ok we got here!
            $image->find($option->getDefaultIconId());
        }

        $iconRelPath = $image->getRelativePath();
        $colorizable = $image->getCanBeColorized();

        if ($colorizable || $forceColorizable) {
            if (!$this->_icon_color) {
                $this->_initIconColor();
            }

            // Moving image to tmp cache
            $ext = pathinfo($iconRelPath, PATHINFO_EXTENSION);
            $tmpAbsPath = path('/var/tmp/image_scol_' . uniqid('', true) . '.' . $ext);
            copy(path($iconRelPath), $tmpAbsPath);

            Core_Model_Lib_Image::sColorize($tmpAbsPath, $this->_icon_color);

            $image = Image::open($tmpAbsPath)->cacheFile('png', 100);

            // Continue normal ops!
            $iconRelPath = str_replace(path(''), '', '/' . $image);
        }

        /** To use immediatly after calling the function */
        static::$lastIconInfos = [
            'colorizable' => $colorizable,
            'relativePath' => $iconRelPath
        ];

        return $iconRelPath;
    }

    /**
     * @param $path
     * @param $hexColor
     * @return bool|false|string
     * @throws Exception
     */
    public function colorizeImage ($path, $hexColor)
    {
        $absPath = path($path);
        list($r, $g, $b) = sscanf($hexColor, "#%02x%02x%02x");
        $image = Image::open($absPath);
        //$image->colorize($r, $g, $b);
        $image->colorize(128, 34, 56);

        // Return png, we must preserve opacity!
        return $image->png(100);
    }


    /**
     * @return $this
     * @throws Zend_Exception
     * @throws Zend_Validate_Exception
     */
    protected function _initIconColor(): self
    {
        $this->_icon_color = '#FFFFFF';
        if (Version::is('PE')) {
            $this->_icon_color = $this->getBlock('border-blue')->getBorderColor();
        }

        return $this;
    }

    /**
     * @return bool|mixed|string
     * @throws Zend_Exception
     */
    public function getLogo()
    {
        try {
            $logo = "";
            if ($this->getCurrentWhiteLabelEditor()) {
                $logo = $this->getCurrentWhiteLabelEditor()->getLogoUrl();
            }

            if (!$logo) {
                $logo = __get("logo");
            }

            if (!$logo) {
                $logo = $this->getImage("header/logo.png");
            }

        } catch (\Exception $e) {
            $logo = $this->getImage("header/logo.png");
        }

        return $logo;
    }
}
