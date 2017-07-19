<?php

class Application_Model_Option extends Core_Model_Default
{

    protected $_category_ids = array(
        1 => ''
    );
    protected $_object;
    protected $_library;
    protected $_layouts;
    protected $_preview;
    protected $_image;
    protected $_icon_url;
    protected $_xml_is_loaded = false;
    protected $_xml = null;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option';
    }

    public function prepareUri() {

        if(defined("APPLICATION_TYPE")) {
            $this->setUri($this->getData(APPLICATION_TYPE.'_uri'));
        }

        return $this;
    }

    public function find($id, $field = null) {
        if($id == 'customer_account') {
            $this->findTabbarAccount();
        }
        else if($id == 'more_items') {
            $this->findTabbarMore();
        }
        else {
            parent::find($id, $field);
        }

        $this->prepareUri();
        return $this;
    }

    public function findTabbarAccount() {
        $user_account = (design_code() == "flat") ? '/tabbar/user_account-flat.png' : '/tabbar/user_account.png';

        $datas = array(
            'option_id' => 'customer_account',
            'design_code' => design_code(),
            'value_id' => 'customer_account',
            'code' => 'tabbar_account',
            'name' => $this->getApplication()->getTabbarAccountName(),
            'tabbar_name' => $this->getApplication()->getTabbarAccountName(),
            'is_ajax' => 0,
            'price' => 0.00,
            'is_active' => 1,
            'desktop_uri' => 'application/customization_features_tabbar_account/',
        );

        $this
            ->setData($datas)
            ->setId('customer_account');

        if($this->getApplication()->getAccountIconId()) {
            $icon_id = $this->getApplication()->getAccountIconId();
            $icon = new Media_Model_Library_Image();
            $icon->find($icon_id);
            $icon_url = $icon->getUrl();

            $this->setIconUrl($icon_url);
            $this->setBaseIconUrl($icon_url);

        } else {
            $this->setIconUrl(Media_Model_Library_Image::getImagePathTo($user_account));
            $this->setBaseIconUrl(Media_Model_Library_Image::getBaseImagePathTo($user_account));
        }

        return $this;
    }

    public function findTabbarMore() {
        $more_items = (design_code() == "flat") ? '/tabbar/more_items-flat.png' : '/tabbar/more_items.png';

        $datas = array(
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
        );

        $this
            ->setData($datas)
            ->setId('more_items');

        if($this->getApplication()->getMoreIconId()) {
            $icon_id = $this->getApplication()->getMoreIconId();
            $icon = new Media_Model_Library_Image();
            $icon->find($icon_id);
            $icon_url = $icon->getUrl();

            $this->setIconUrl($icon_url);
            $this->setBaseIconUrl($icon_url);

        } else {
            $this->setIconUrl(Media_Model_Library_Image::getImagePathTo($more_items));
            $this->setBaseIconUrl(Media_Model_Library_Image::getBaseImagePathTo($more_items));
        }

        return $this;
    }


    public function delete() {
        if($this->getObject()->getId()) {
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
    public function getEmbedPayload($request = null) {
        if($request !== null) {
            $this->setBaseUrl($request->getBaseUrl());
            $this->setRequest($request);
        } else {
            $this->setBaseUrl("");
            $this->setRequest(null);
        }

        return $this->getObject()->getEmbedPayload($this);
    }

    public function getObject() {
        if(!$this->_object) {
            if($class = $this->getModel()) {

                try {
                    if(!class_exists($class)) {
                        throw new Siberian_Exception("The current class doesn't exists {$class}");
                    }
                    $this->_object = new $class();
                    $this->_object->find($this->getValueId(), 'value_id');
                } catch (Exception $e) {
                    $this->_object = class_exists($class) ? new $class() : new Core_Model_Default();
                }
            } else {
                $this->_object = new Core_Model_Default();
            }
        }

        return $this->_object;
    }

    public function getName() {
        return __($this->getData('name'));
    }

    public function getUri() {
        if(!$this->getData("uri")) {
            $this->prepareUri();
        }

        return $this->getData("uri");
    }

    public function getTabbarName() {
        return $this->getData('tabbar_name') ? __(mb_convert_encoding($this->getData('tabbar_name'), 'UTF-8', 'UTF-8')) : null;
    }

    public function getShortTabbarName() {
        $name = $this->getTabbarName();
        return Core_Model_Lib_String::formatShortName($name);
    }

    public function getLayouts() {

        if(empty($this->_layouts)) {
            $layout = new Application_Model_Option_Layout();
            $this->_layouts = $layout->findAll(array("option_id = ?" => $this->getOptionId()));
        }

        return $this->_layouts;

    }

    /**
     * Overrides with design taken into account
     *
     * Flat 4.2.0
     *
     * @param bool $base
     * @return string
     */
    public function getIconUrl($base = false) {

        if(empty($this->_icon_url) AND $this->getIconId()) {
            if($this->getIcon() AND !$base) {
                $this->_icon_url = Media_Model_Library_Image::getImagePathTo($this->getIcon(), $this->getAppId());
            }
            else {
                $icon = new Media_Model_Library_Image();
                $icon->find($this->getDefaultIconId());
                $this->_icon_url = $icon->getUrl();
            }
        }

        return $this->_icon_url;
    }

    public function getDefaultIconId() {
        $library = $this->getLibrary();

        $icon = $library->getFirstIcon();

        return $icon->getId();
    }

    public function setIconUrl($url) {
        $this->_icon_url = $url;
        return $this;
    }

    public function resetIconUrl() {
        $this->_icon_url = null;
        return $this;
    }

    public function getImage() {

        if(empty($this->_image)) {
            $this->_image = new Media_Model_Library_Image();
            if($this->getIconId()) $this->_image->find($this->getIconId());
        }

        return $this->_image;
    }

    public function resetImage() {
        $this->_image = null;
        return $this;
    }

    public function onlyOnce() {
        return $this->getData('only_once');
    }

    public function isLink() {
        return (bool) $this->getObject() && $this->getObject()->getLink();
    }

    public function getUrl($action, $params = array(), $feature_url = true, $env = null) {

        $url = null;
//        if($this->getIsDummy()) {
//            $url = '#';
//        }
//        else
        if($this->getUri()) {
            $uri = $this->getUri();
            if(!is_null($env) AND $this->getData("{$env}_uri")) {
                $uri = $this->getData("{$env}_uri");
            }

            if(!$feature_url AND $env != "desktop" AND !$this->getIsAjax() AND $this->getObject()->getLink()) {
                $url = (string) $this->getObject()->getLink()->getUrl();
            }
            else $url = parent::getUrl($uri.$action, $params);
        }
        else {
            $url = '/front/index/noroute';
        }

        return $url;
    }

    public function getPath($action, $params = array(), $env = null) {

        if($this->getValueId()) {
            $params["value_id"] = $this->getValueId();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $use_key = $request->useApplicationKey();
        $path = null;
        $force_uri = stripos($action, "/") !== false;

        $uri = $force_uri ? $action : $this->getUri();

        if($uri) {

            if(!is_null($env)) {

                if(!$force_uri) $uri .= $action;

                if($this->getData("{$env}_uri")) {
                    $uri = $this->getData("{$env}_uri");
                }

                if($env == "mobile") {
                    $request->useApplicationKey(true);
                }
            }

            if($env != "desktop" AND !$this->getIsAjax() AND $this->getObject()->getLink()) $path = $this->getObject()->getLink()->getUrl();
            else $path = parent::getPath($uri, $params);

        }
        else {
            $path = '/front/index/noroute';
        }

        $request->useApplicationKey($use_key);

        return $path;
    }

    public function getMobileViewUri($action, $params = array()) {
        $uri = null;

        if($uri = $this->getData("mobile_view_uri")) $uri .= $action;

        foreach($params as $key => $value) {
            if(!empty($value)) $uri .= "/$key/$value";
        }

        return $uri;
    }

    public function getPreview() {
        if(!$this->_preview) {
            $preview = new Preview_Model_Preview();
            $language = Core_Model_Language::getCurrentLanguage();
            $this->_preview = $preview->find(array(
                "aop.option_id" => $this->getId(),"aopl.language_code" => $language));
        }
        return $this->_preview;
    }


    /**
     * Fetch the Library associated with this option, regarding the Design (siberian, flat, ...)
     */
    public function getLibrary() {
        if(empty($this->_library)) {
            $library = new Media_Model_Library();
            $this->_library = $library->getLibraryForDesign($this->getLibraryId());
        }

        return $this->_library;
    }

    public function getCustomFields() {
        $custom_fields = json_decode($this->getData('custom_fields'), true);
        return is_array($custom_fields) ? $custom_fields : array();
    }
}
