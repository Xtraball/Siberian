<?php

abstract class Core_Controller_Default_Abstract extends Zend_Controller_Action
{

    protected $_layout;
    protected static $_application;
    protected static $_session = array();

    public function init() {

        $this->_initDesign();
        $this->_initSession();
        $this->_initAcl();

        $this->_initLanguage();
        $this->_initLocale();

        if($url = $this->_needToBeRedirected()) {
            $this->_redirect($url, $this->getRequest()->getParams());
            return $this;
        }

        $this->_initTranslator();

        $this->_layout = $this->_helper->layout->getLayoutInstance();

        if(preg_match('/(?i)msie \b[5-9]\b/',$this->getRequest()->getHeader('user_agent')) && !preg_match('/(oldbrowser)/', $this->getRequest()->getActionName())) {
            $message = $this->_("Your browser is too old to view the content of our website.<br />");
            $message .= $this->_("In order to fully enjoy our features, we encourage you to use at least:.<br />");
            $message .= '- Internet Explorer 10 ;<br />';
            $message .= '- Firefox 3.5 ;<br />';
            $message .= '- Chrome 8 ;<br />';
            $message .= '- Safari 6 ;<br />';

            $this->getSession()->addWarning($message, 'old_browser');

        }

    }

    public function _($text) {
        $args = func_get_args();
        return Core_Model_Translator::translate($text, $args);
    }

    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            return $this->_forward('noroute');
        }

        throw new Exception('Méthode invalide "' . $method . '" appelée',500);
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

        if(!$type) $type = SESSION_TYPE;

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
        return Application_Model_Application::getInstance();
    }

    public function loadPartials($action = null, $use_base = null) {
        if(is_null($use_base)) $use_base = true;
        if(is_null($action)) $action = $this->getFullActionName('_');
        $this->getLayout()->setAction($action)->load($use_base);

        return $this;
    }

    public function render($action = null, $name = null, $noController = false) {

    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $action = 'noroute';
                break;

            default:
                $this->getResponse()->setHttpResponseCode(500);
                $action = 'exception';
                break;
        }

        $this->forward($action);
    }

    public function oldbrowserAction() {
        $this->loadPartials('front_index_oldbrowser');
        return $this;
    }

    public function preDispatch() {
        parent::preDispatch();
    }

    public function postDispatch() {

        if(!$this->getLayout()->isLoaded() AND $this->getRequest()->isDispatched()) {
            $this->_forward('noroute');
        }

        parent::postDispatch();

    }

    public function norouteAction() {

        if(!$this->getRequest()->isApplication()) {
            $this->getResponse()->setHeader('HTTP/1.0', '404 Not Found');
            $this->loadPartials('front_index_noroute');
        } else {
            $this->forward("index", "index");
        }

    }

    public function forbiddenAction() {

        if(!$this->getRequest()->isApplication()) {
            $this->getResponse()
                    ->clearHeaders()
                    ->setHttpResponseCode(403)
            ;

            $this->loadPartials('front_index_forbidden');
        } else {
            $this->forward("index", "index");
        }

    }

    public function exceptionAction() {

        $errors = $this->_getParam('error_handler');

        $logger = Zend_Registry::get("logger");
        $logger->sendException("Fatal Error: \n".print_r($errors, true));
    }

    public function getLayout() {
        return $this->_layout;
    }

    public function getFullActionName($separator = '/') {

        return strtolower(join($separator, array(
            $this->getRequest()->getModuleName(),
            $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName()
        )));

    }

    public function getUrl($url = '', array $params = array(), $locale = null) {
        return Core_Model_Url::create($url, $params, $locale);
    }

    public function getPath($uri = '', array $params = array()) {
        return Core_Model_Url::createPath($uri, $params);
    }

    public function getCurrentUrl($withParams = true, $locale = null) {
        return Core_Model_Url::current($withParams, $locale);
    }

    public function downloadAction() {

        $path = $this->getRequest()->getParam('path');
        $path = base64_decode($path);
        $name = $this->getRequest()->getParam('name');
        $name = base64_decode($name);

        $content_type = $this->getRequest()->getParam('content_type');

        $this->_download($path, $name, $content_type);

    }

    protected function _getImage($name, $base = false) {

        if(file_exists(Core_Model_Directory::getDesignPath(true) . '/images/' . $name)) {
            return Core_Model_Directory::getDesignPath($base).'/images/'.$name;
        }
        else if(file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);
        }

        return "";

    }

    protected function _getColorizedImage($image_id, $color) {

        $color = str_replace('#', '', $color);
        $id = md5(implode('+', array($image_id, $color)));
        $url = '';

        $image = new Media_Model_Library_Image();
        if(is_numeric($image_id)) {
            $image->find($image_id);
            if(!$image->getId()) return $url;
            if(!$image->getCanBeColorized()) $color = null;
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path, $image->getAppId());
        } else if(!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
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

    protected function _redirect($url, array $options = array()) {
        $url = Core_Model_Url::create($url, $options);
        parent::_redirect($url, $options);
    }

    protected function _initDesign() {

        $detect = new Mobile_Detect();

        if(!$this->getRequest()->isInstalling()) {
            $design_code =  System_Model_Config::getValueFor("editor_design");
        } else {
            $design_code = "installer";
        }

        $design_codes = array(
            "desktop" => $design_code,
            "mobile" => "angular"
        );

        $white_label_blocks = array(
            "flat" => array(
                "block" => "color-blue",
                "color" => "background_color",
                "color_reverse" => "color"
            ),
            "siberian" => array(
                "block" => "area",
                "color" => "color"
            )
        );

        Zend_Registry::set("design_codes", $design_codes);
        Siberian_Cache_Design::$design_codes = $design_codes;

        if(!$this->getRequest()->isInstalling()) {
            if($this->getRequest()->isApplication()) $apptype = 'mobile';
            else $apptype = 'desktop';
            if($detect->isMobile() || $apptype == 'mobile') $device_type = 'mobile';
            else $device_type = 'desktop';
            if($this->getRequest()->isApplication()) $code = $design_codes["mobile"];
            else if($this->_isInstanceOfBackoffice()) $code = 'backoffice';
            else $code = $design_codes["desktop"];
        } else {
            $apptype = 'desktop';
            $device_type = 'desktop';
            $code = "installer";
        }

        $base_paths = array(APPLICATION_PATH . "/design/email/template/");

        if(!defined("APPLICATION_TYPE")) define("APPLICATION_TYPE", $apptype);
        if(!defined("DEVICE_TYPE"))      define("DEVICE_TYPE", $device_type);
        if(!defined("DEVICE_IS_IPHONE")) define("DEVICE_IS_IPHONE", $detect->isIphone() || $detect->isIpad());
        if(!defined("IS_APPLICATION"))   define("IS_APPLICATION", $detect->isNative() && $this->getRequest()->isApplication());
        if(!defined("DESIGN_CODE"))      define("DESIGN_CODE", $code);

        Core_Model_Directory::setDesignPath("/app/sae/design/$apptype/$code");

        $resources = array(
            'resources' => array(
                'layout' => array('layoutPath' => APPLICATION_PATH . "/design/$apptype/$code/template/page")
            )
        );

        $base_paths[] = APPLICATION_PATH . "/design/$apptype/$code/template/";

        $bootstrap = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $bootstrap->setOptions($resources);

        $bootstrap->bootstrap('View');
        $view = $bootstrap->getResource('View');
        $view->doctype('HTML5');

        foreach($base_paths as $base_path) {
            $view->addBasePath($base_path);
        }

        Core_View_Default::setDevice($detect);
        Application_Controller_Mobile_Default::setDevice($detect);

        if(!$this->getRequest()->isInstalling()) {

            $blocks = array();
            if ($this->getRequest()->isApplication()) {
                $blocks = $this->getRequest()->getApplication()->getBlocks();
            } else if(!$this->_isInstanceOfBackoffice()) {
                $blocks = $this->getRequest()->getWhiteLabelEditor()->getBlocks();

                if($block = $this->getRequest()->getWhiteLabelEditor()->getBlock($white_label_blocks[DESIGN_CODE]["block"])) {
                    $icon_color = $block->getData($white_label_blocks[DESIGN_CODE]["color"]);
                    Application_Model_Option_Value::setEditorIconColor($icon_color);

                    if($reverse_color = $white_label_blocks[DESIGN_CODE]["color_reverse"]) {
                        Application_Model_Option_Value::setEditorIconReverseColor($block->getData($reverse_color));
                    }
                }

            }

            if (!empty($blocks)) {
                Core_View_Default::setBlocks($blocks);
            }

        }

    }

    protected function _initSession() {

        if(Zend_Session::isStarted()) return $this;
        
        $configSession = new Zend_Config_Ini(APPLICATION_PATH . '/configs/session.ini', APPLICATION_ENV);

        if(!$this->getRequest()->isInstalling()) {
            $config = array(
                'name'           => 'session',
                'primary'        => 'session_id',
                'modifiedColumn' => 'modified',
                'dataColumn'     => 'data',
                'lifetimeColumn' => 'lifetime',
                'lifetime'       => $configSession->gc_maxlifetime
            );

            Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
        }

        if(!$this->getRequest()->isInstalling() OR is_writable(Core_Model_Directory::getSessionDirectory(true))) {

            $options = $configSession->toArray();

            if($sid = $this->getRequest()->getParam("sb-token")) {
                Zend_Session::setId($sid);
            }
            Zend_Session::start($options);

            $session_type = 'front';

            if($this->getRequest()->isApplication()) {
                if(Siberian_Version::is('sae')) {
                    $session_type = 'mobile';
                } else {
                    $session_type = 'mobile'.$this->getRequest()->getApplication()->getAppId();
                }
            }
            else if($this->_isInstanceOfBackoffice()) {
                $session_type = 'backoffice';
            }

            defined('SESSION_TYPE')
                || define('SESSION_TYPE', $session_type);

            $session = new Core_Model_Session($session_type);

            Core_Model_Language::setSession($session);
            Core_View_Default::setSession($session, $session_type);
            Core_Model_Default::setSession($session, $session_type);
            self::setSession($session, $session_type);

        }

    }

    protected function _initAcl() {

        if(!$this->getRequest()->isInstalling()) {

            $is_editor = !$this->getRequest()->isApplication() && !$this->_isInstanceOfBackoffice();
            if($is_editor AND $this->getSession()->isLoggedIn()) {

                $acl = new Acl_Model_Acl();
                $acl->prepare($this->getSession()->getAdmin());

                Core_View_Default::setAcl($acl);
                Admin_Controller_Default::setAcl($acl);

            }
        }

    }

    protected function _initLanguage() {

        $available_languages = Core_Model_Language::getLanguageCodes();
        $current_language = in_array($this->getRequest()->getLanguageCode(), $available_languages) ? $this->getRequest()->getLanguageCode() : "";
        $language_session = Core_Model_Language::getSession();
        $language = '';

        if(!$this->getRequest()->isApplication()) {

            if($language_session->current_language) {
                $language = $language_session->current_language;
            } else if(!$this->getRequest()->isInstalling()) {
                $current_language = System_Model_Config::getValueFor("system_default_language");
            }

        } else {
            $language = $language_session->current_language;
        }

        if(!empty($current_language)) {
            Core_Model_Language::setCurrentLanguage($current_language);
        } else if(!empty($language)) {
        } else if($accepted_languages = Zend_Locale::getBrowser()) {
            $accepted_languages = array_keys($accepted_languages);
            foreach($accepted_languages as $lang) {
                if(in_array($lang, $available_languages)) {
                    $current_language = $lang;
                    break;
                }
            }

            if(!$current_language) {
                $current_language = Core_Model_Language::getDefaultLanguage();
            }

            Core_Model_Language::setCurrentLanguage($current_language);

        } else {
            Core_Model_Language::setCurrentLanguage(Core_Model_Language::getDefaultLanguage());
        }

    }

    protected function _initLocale() {

        $locale = new Zend_Locale();
        $locale_code = Core_Model_Language::DEFAULT_LOCALE;

        $is_installing = $this->getRequest()->isInstalling();

        if($this->getRequest()->isApplication() && $this->getApplication()->getLocale()) {
            $locale_code = $this->getApplication()->getLocale();
        } else if(!$is_installing) {

            $currency_code = System_Model_Config::getValueFor("system_currency");
            if($currency_code) {
                $currency = new Zend_Currency(null, $currency_code);
                Core_Model_Language::setCurrentCurrency($currency);
            }

            $territory = System_Model_Config::getValueFor("system_territory");
            if($territory) {
                $locale_code = $locale->getLocaleToTerritory($territory);
            } else {
                $locale_code = new Zend_Locale(Core_Model_Language::getCurrentLocale());
            }

        }

        if(!$is_installing) {
            $timezone = System_Model_Config::getValueFor("system_timezone");
            if($timezone) {
                date_default_timezone_set($timezone);
            }
        }

        $locale->setLocale($locale_code);

        Zend_Registry::set('Zend_Locale', $locale);

    }

    protected function _initTranslator() {
        Core_Model_Translator::prepare(strtolower($this->getRequest()->getModuleName()));
        return $this;
    }

    protected function _needToBeRedirected() {

        $url = null;

        if($this->getRequest()->isInstalling()) {
            if(!$this->getRequest()->isXmlHttpRequest() AND !in_array($this->getFullActionName('_'), array('front_index_index', 'front_error_error', 'front_error_exception'))) {
                $url = '/';
            }
        }

        if($this->getRequest()->getLanguageCode()) {
            $url = is_null($url) ? $this->getRequest()->getPathInfo() : $url;
        }

        return $url;
    }

    protected function _sendHtml($html) {
        if(!empty($html['error'])) {
            $this->getResponse()->setHttpResponseCode(400);
        }

        /** Adding debug errors to the returning json */
        if(APPLICATION_ENV == 'development') {
            /** @note disabled until done */
            //$html['debug'] = Siberian_Error::$errors;
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     * Download a file or data
     *
     * @param $file Path or RAW Data
     * @param $filename
     * @param string $content_type
     */
    protected function _download($file, $filename, $content_type = 'application/vnd.ms-excel') {

        $response = $this->getResponse();

        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-Type', $content_type);
        $response->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Last-Modified', date('r'));

        if(file_exists($file)) {

            $response->setHeader('Content-Length', filesize($file));
            $response->sendHeaders();

            ob_end_flush();

            readfile($file); /** Avoid storing the whole file in memory  */
        }
        else {
            $response->setHeader('Content-Length', strlen($file));
            $response->setBody($file);
            $response->sendResponse();
        }

        exit();
    }

    protected function _setBaseLayout($layout) {
        $this->_helper->layout()->setLayout($layout);
        return $this;
    }

    protected function _isInstanceOfBackoffice() {
        return is_subclass_of($this, 'Backoffice_Controller_Default');
    }

}