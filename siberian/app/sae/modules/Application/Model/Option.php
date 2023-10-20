<?php

use Siberian\Version;

/**
 * Class Application_Model_Option
 *
 * @method integer getId()
 */
class Application_Model_Option extends Core_Model_Default
{

    /**
     * @var array
     */
    protected $_category_ids = [
        1 => ''
    ];
    /**
     * @var
     */
    protected $_object;
    /**
     * @var
     */
    protected $_library;
    /**
     * @var
     */
    protected $_layouts;
    /**
     * @var
     */
    protected $_preview;
    /**
     * @var
     */
    protected $_image;
    /**
     * @var
     */
    protected $_icon_url;
    /**
     * @var bool
     */
    protected $_xml_is_loaded = false;
    /**
     * @var null
     */
    protected $_xml = null;

    /**
     * @var string
     */
    protected $_db_table = Application_Model_Db_Table_Option::class;

    /**
     * @return $this
     */
    public function prepareUri()
    {

        if (defined("APPLICATION_TYPE")) {
            $this->setUri($this->getData(APPLICATION_TYPE . '_uri'));
        }

        return $this;
    }

    /**
     * @param $id
     * @param null $field
     * @return $this|null
     */
    public function find($id, $field = null)
    {
        if ($id === 'customer_account') {
            $this->findTabbarAccount();
        } else if ($id === 'more_items') {
            $this->findTabbarMore();
        } else {
            parent::find($id, $field);
        }

        $this->prepareUri();
        return $this;
    }

    /**
     * @return $this
     */
    public function findTabbarAccount()
    {
        $application = $this->getApplication();

        $datas = [
            'option_id' => 'customer_account',
            'design_code' => design_code(),
            'value_id' => 'customer_account',
            'code' => 'tabbar_account',
            'name' => $application->getTabbarAccountName(),
            'tabbar_name' => $application->getTabbarAccountName(),
            'is_ajax' => 0,
            'price' => 0.00,
            'is_active' => 1,
            'desktop_uri' => 'application/customization_features_tabbar_account/',
        ];

        $this
            ->setData($datas)
            ->setId('customer_account');

        // @todo review icons
        if ($application->getAccountIconId()) {
            $icon_id = $application->getAccountIconId();
            $icon = new Media_Model_Library_Image();
            $icon->find($icon_id);
            $icon_url = $icon->getUrl();

            $this->setIconUrl($icon_url);
            $this->setBaseIconUrl($icon_url);

        } else {
            $this->setIconUrl(Media_Model_Library_Image::getImagePathTo('/tabbar/user_account-flat.png'));
            $this->setBaseIconUrl(Media_Model_Library_Image::getBaseImagePathTo('/tabbar/user_account-flat.png'));
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Zend_Exception
     */
    public function findTabbarMore(): self
    {
        $application = $this->getApplication();
        $moreItems = '/tabbar/more_items-flat.png';

        $datas = [
            'option_id' => 'more_items',
            'design_code' => design_code(),
            'value_id' => 'more_items',
            'code' => 'tabbar_more',
            'name' => $this->getApplication()->getTabbarMoreName(),
            'tabbar_name' => $this->getApplication()->getTabbarMoreName(),
            'is_ajax' => 0,
            'price' => 0.00,
            'is_active' => 1,
            'desktop_uri' => 'application/customization_features_tabbar_more/',
        ];

        $this
            ->setData($datas)
            ->setId('more_items');

        if ($application->getMoreIconId()) {
            $icon_id = $application->getMoreIconId();
            $icon = (new Media_Model_Library_Image())->find($icon_id);
            $iconUrl = $icon->getRelativePath();

            $this->setIconUrl($iconUrl);
            $this->setBaseIconUrl(path($iconUrl));
        } else {
            $this->setIconUrl(Media_Model_Library_Image::getImagePathTo($moreItems));
            $this->setBaseIconUrl(Media_Model_Library_Image::getBaseImagePathTo($moreItems));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        if ($this->getObject()->getId()) {
            $this->getObject()->delete();
        }
        return parent::delete();
    }

    /**
     * Shortcut for embed payload
     *
     * @param null $request
     * @return bool
     */
    public function getEmbedPayload($request = null)
    {
        if ($request !== null) {
            $this->setBaseUrl($request->getBaseUrl());
            $this->setRequest($request);
        } else {
            $this->setBaseUrl("");
            $this->setRequest(null);
        }

        return $this->getObject()->getEmbedPayload($this);
    }

    /**
     * @return Core_Model_Default
     */
    public function getObject()
    {
        if (!$this->_object) {
            if ($class = $this->getModel()) {
                try {
                    if (!class_exists($class)) {
                        throw new Siberian_Exception("The current class doesn't exists {$class}");
                    }
                    $this->_object = new $class(); // New class on line ensure the object exists at least!
                    $this->_object->find($this->getValueId(), 'value_id');
                } catch (Exception $e) {
                    $this->_object = new Core_Model_Default();
                }
            } else {
                $this->_object = new Core_Model_Default();
            }
        }

        // Last failsafe!
        if (empty($this->_object) ||
            is_null($this->_object)) {
            $this->_object = new Core_Model_Default();
        }

        return $this->_object;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return __($this->getData('name'));
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        if (!$this->getData("uri")) {
            $this->prepareUri();
        }

        return $this->getData("uri");
    }

    /**
     * @return null
     */
    public function getTabbarName()
    {
        return $this->getData("tabbar_name") ?
            __(mb_convert_encoding($this->getData("tabbar_name"), "UTF-8", "UTF-8")) : null;
    }

    /**
     * @return string
     */
    public function getShortTabbarName()
    {
        $name = $this->getTabbarName();
        return Core_Model_Lib_String::formatShortName($name);
    }

    /**
     * @return mixed
     */
    public function getLayouts()
    {
        if (empty($this->_layouts)) {
            $layout = new Application_Model_Option_Layout();
            $this->_layouts = $layout->findAll(["option_id = ?" => $this->getOptionId()]);
        }

        return $this->_layouts;
    }

    /**
     * @param bool $base
     * @return string
     * @throws Zend_Exception
     */
    public function getIconUrl($base = false): string
    {
        if (empty($this->_icon_url)) {
            $icon = new Media_Model_Library_Image();
            $icon->find($this->getIconId());
            if (!$icon->checkFile()) {
                $icon->find($this->getDefaultIconId());
            }
            $this->_icon_url = $icon->getRelativePath();
        }

        return $base ? path($this->_icon_url) : $this->_icon_url;
    }

    /**
     * @return mixed
     */
    public function getDefaultIconId()
    {
        try {
            $library = $this->getLibrary();
            $icon = $library->getFirstIcon();
            return $icon->getId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $url
     * @return $this
     */
    public function setIconUrl($url): self
    {
        $this->_icon_url = $url;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetIconUrl(): self
    {
        $this->_icon_url = null;
        return $this;
    }

    /**
     * @return Media_Model_Library_Image
     */
    public function getImage(): \Media_Model_Library_Image
    {
        if (empty($this->_image)) {
            $this->_image = new Media_Model_Library_Image();
            if ($this->getIconId()) {
                $this->_image->find($this->getIconId());
            }
        }

        return $this->_image;
    }

    /**
     * @return $this
     */
    public function resetImage(): self
    {
        $this->_image = null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function onlyOnce()
    {
        return $this->getData('only_once');
    }

    /**
     * @return bool
     * @throws Zend_Exception
     */
    public function isLink()
    {
        return (bool)$this->getObject() && $this->getObject()->getLink();
    }

    /**
     * @param string $action
     * @param array $params
     * @param bool $feature_url
     * @param null $env
     * @return array|mixed|null|string
     * @throws Zend_Exception
     */
    public function getUrl($action = '', $params = [], $feature_url = true, $env = null)
    {
        $url = null;
        if ($this->getUri()) {
            $uri = $this->getUri();
            if (!is_null($env) &&
                $this->getData("{$env}_uri")) {
                $uri = $this->getData("{$env}_uri");
            }

            if (!$feature_url &&
                $env !== "desktop" &&
                !$this->getIsAjax() &&
                $this->getObject()->getLink()) {

                $url = (string)$this->getObject()->getLink()->getUrl();
            } else {
                $url = parent::getUrl($uri . $action, $params);
            }
        } else {
            $url = '/front/index/noroute';
        }

        return $url;
    }

    /**
     * @param $action
     * @param array $params
     * @param null $env
     * @return null|string
     */
    public function getPath($action = '', array $params = [], $env = null)
    {
        if ($this->getValueId()) {
            $params['value_id'] = $this->getValueId();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $appUseKey = Version::is(['SAE']) ?
            $request->useApplicationKey() : $request->getApplicationKey();
        $path = null;
        $force_uri = stripos($action ?? "", "/") !== false;

        $uri = $force_uri ? $action : $this->getUri();

        if ($uri) {

            if (!is_null($env)) {

                if (!$force_uri) {
                    $uri .= $action;
                }

                if ($this->getData("{$env}_uri")) {
                    $uri = $this->getData("{$env}_uri");
                }

                if (in_array($env, ['mobile', 'mobile_custom'], true)) {
                    if (Version::is(['SAE'])) {
                        $request->useApplicationKey(true);
                    } else {
                        $request->setApplicationKey($this->getApplication()->getKey());
                    }
                }
            }

            if ($env !== 'desktop' &&
                !$this->getIsAjax() &&
                $this->getObject()->getLink()) {
                $path = $this->getObject()->getLink()->getUrl();
            } else {
                $path = parent::getPath($uri, $params);
            }
        } else {
            $path = '/front/index/noroute';
        }

        if (Version::is(['SAE'])) {
            $request->useApplicationKey($appUseKey);
        } else {
            $request->setApplicationKey($appUseKey);
        }

        return $path;
    }

    /**
     * @param $action
     * @param array $params
     * @return null|string
     */
    public function getMobileViewUri($action, $params = [])
    {
        $uri = null;

        if ($uri = $this->getData("mobile_view_uri")) $uri .= $action;

        foreach ($params as $key => $value) {
            if (!empty($value)) $uri .= "/$key/$value";
        }

        return $uri;
    }

    /**
     * @return mixed
     */
    public function getPreview()
    {
        if (!$this->_preview) {
            $preview = new Preview_Model_Preview();
            $language = Core_Model_Language::getCurrentLanguage();
            $this->_preview = $preview->find([
                "aop.option_id" => $this->getId(), "aopl.language_code" => $language]);
        }
        return $this->_preview;
    }

    /**
     * @return Media_Model_Library
     */
    public function getLibrary(): \Media_Model_Library
    {
        if (empty($this->_library)) {
            $library = new Media_Model_Library();
            $this->_library = $library->getLibraryForDesign($this->getLibraryId());
        }

        return $this->_library;
    }

    /**
     * @return array|mixed
     */
    public function getCustomFields()
    {
        $custom_fields = json_decode($this->getData('custom_fields'), true);
        return is_array($custom_fields) ? $custom_fields : [];
    }
}
