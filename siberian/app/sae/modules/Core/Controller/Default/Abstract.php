<?php

/**
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */

use Siberian_Media as Media;
use Siberian\ClamAV;
use Siberian\Json;
use Siberian\Security;

/**
 * Class Core_Controller_Default_Abstract
 *
 * @method $this setLayoutVisibility(string $visiblity)
 */
abstract class Core_Controller_Default_Abstract extends Zend_Controller_Action implements Core_Model_Exporter
{
    /**
     * @var Zend_Cache_Backend_File|Zend_Cache
     */
    public $cache;

    /**
     * @var Zend_Cache_Frontend_Output
     */
    public $cacheOutput;

    /**
     * @var array|null
     */
    public $cache_triggers = null;

    /**
     * @var
     */
    protected $_layout;

    /**
     * @var
     */
    protected static $_application;

    /**
     * @var array
     */
    protected static $_session = [];

    /**
     * @var
     */
    protected $float_validator;

    /**
     * @var
     */
    protected $int_validator;

    /**
     * @param $val
     * @return mixed
     */
    public function validateFloat($val)
    {
        return $this->float_validator->isValid($val);
    }

    /**
     * @param $val
     * @return mixed
     */
    public function validateInt($val)
    {
        return $this->int_validator->isValid($val);
    }

    /**
     * @return Siberian_Controller_Request_Http
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * @return $this|void
     * @throws Zend_Exception
     * @throws \Siberian\Exception
     */
    public function init()
    {
        $this->cache = Zend_Registry::get('cache');
        $this->cacheOutput = Zend_Registry::get('cacheOutput');

        $request = $this->getRequest();

        $this->_initDesign();
        $this->_initSession();
        $this->_initAcl();
        $this->_initLanguage();
        $this->_initLocale();

        $this->float_validator = new Zend_Validate_Float();
        $this->int_validator = new Zend_Validate_Int();

        if ($url = $this->_needToBeRedirected()) {
            $this->_redirect($url, $this->getRequest()->getParams());
            return $this;
        }

        $this->_initTranslator();

        $this->_layout = $this->_helper->layout->getLayoutInstance();

        // Firewall filtering rules!
        $session = $this->getSession();

        if ($session->getAdminId()) {
            $session->getAdmin()->updateLastAction();
        }

        // Upload APK clamav trigger
        $routeName = $this->getFullActionName();
        if ('application/backoffice_iosautopublish/uploadapk' === $routeName) {
            ClamAv::disableTemporary();
        }

        // If the WAF is enabled!
        if (!$request->isInstalling() &&
            Security::isEnabled()) {
            // Checking inside the whitelist!
            if (!Security::isWhitelisted($routeName)) {
                if (!empty($_FILES)) {
                    Security::filterFiles($_FILES, $session);
                }

                if (!empty($_GET)) {
                    Security::filterGet($_GET, $session);
                }

                if (!empty($_POST)) {
                    Security::filterPost($_POST, $session);
                }

                $bodyParams = $this->getRequest()->getBodyParams();
                if (!empty($bodyParams)) {
                    Security::filterBodyParams($bodyParams, $session);
                }
            }
        }
    }

    /**
     *
     */
    public function preDispatch()
    {
        # Check for cache triggers and call them
        $this->_triggerCache();

        parent::preDispatch();
    }

    /**
     * This method is used to automatically clean cache tags, with triggers defined in each controllers
     *
     * We are using an infinite cache policy, with clear triggers.
     *
     */
    public function _triggerCache()
    {

        $request = $this->getRequest();
        $response = $this->getResponse();
        $session = $this->getSession();

        if (isset($this->cache_triggers) && is_array($this->cache_triggers)) {

            $action_name = $this->getRequest()->getActionName();

            $current_language = Core_Model_Language::getCurrentLanguage();

            if (isset($this->cache_triggers[$action_name])) {

                $adminId = $session->getAdminId();
                $params = $request->getParams();
                $payload_data = Json::decode($request->getRawBody());
                $valueId = null;
                if (isset($params['value_id']) && !empty($params['value_id'])) {
                    $valueId = $params['value_id'];
                } else if (isset($params['option_value_id']) && !empty($params['option_value_id'])) {
                    $valueId = $params['option_value_id'];
                } else if (isset($payload_data['value_id']) && !empty($payload_data['value_id'])) {
                    $valueId = $payload_data['value_id'];
                } else if (isset($payload_data['option_value_id']) && !empty($payload_data['option_value_id'])) {
                    $valueId = $payload_data['option_value_id'];
                }

                # App_id
                $app = $this->getApplication();
                $appId = 'noapps';
                if ($app) {
                    $appId = $app->getId();
                }

                if (empty($appId) || ($appId === 'noapps')) {
                    # Search in params/payload
                    if (isset($params['app_id']) && !empty($params['app_id'])) {
                        $appId = $params['app_id'];
                    } else if (isset($payload_data['app_id']) && !empty($payload_data['app_id'])) {
                        $appId = $payload_data['app_id'];
                    }
                }

                $values = $this->cache_triggers[$action_name];
                if (isset($values['tags']) &&
                    is_array($values['tags'])) {

                    $finalTags = [];
                    foreach ($values['tags'] as $tag) {
                        $finalTags[] = str_replace(
                            [
                                '#APP_ID#',
                                '#VALUE_ID#',
                                '#LOCALE#',
                            ],
                            [
                                $appId,
                                $valueId,
                                $current_language,
                            ],
                            $tag
                        );
                    }

                    # Clean-up
                    $this->cache->clean(
                        Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                        $finalTags
                    );

                    $response->setHeader('x-cache-clean', implode_polyfill(', ', $finalTags));
                }

                if (isset($values['outputTags']) &&
                    is_array($values['outputTags'])) {

                    $finalTags = [];
                    foreach ($values['outputTags'] as $outputTag) {
                        $finalTags[] = str_replace(
                            [
                                '#APP_ID#',
                                '#ADMIN_ID#',
                                '#VALUE_ID#',
                                '#LOCALE#',
                            ],
                            [
                                $appId,
                                $adminId,
                                $valueId,
                                $current_language,
                            ],
                            $outputTag
                        );
                    }

                    # Clean-up
                    $this->cacheOutput->clean(
                        Zend_Cache::CLEANING_MODE_ALL,
                        $finalTags
                    );

                    $response->setHeader('x-cache-output-clean', implode_polyfill(', ', $finalTags));
                }
            }
        }
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public function _($text)
    {
        return call_user_func_array('__', func_get_args());
    }

    /**
     * @param string $method
     * @param array $args
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if ('Action' === substr($method, -6)) {
            return $this->_forward('noroute');
        }

        throw new Exception('Méthode invalide "' . $method . '" appelée', 500);
    }

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return APPLICATION_ENV === 'production';
    }

    /**
     * @return bool
     */
    public function isSae(): bool
    {
        return Siberian_Version::TYPE === 'SAE';
    }

    /**
     * @return bool
     */
    public function isMae(): bool
    {
        return Siberian_Version::TYPE === 'MAE';
    }

    /**
     * @return bool
     */
    public function isPe(): bool
    {
        return Siberian_Version::TYPE === 'PE';
    }

    /**
     * @param null $type
     * @return Core_Model_Session|mixed
     * @throws Zend_Session_Exception
     */
    public function getSession($type = null)
    {
        if (!$type) {
            $type = defined('SESSION_TYPE') ? SESSION_TYPE : 'admin';
        }

        if (isset(self::$_session[$type])) {
            return self::$_session[$type];
        }

        $session = new Core_Model_Session($type);
        self::setSession($session, $type);
        return $session;
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
     * @return Application_Model_Application
     * @throws Zend_Exception
     */
    public function getApplication()
    {
        return Application_Model_Application::getInstance();
    }

    /**
     * @param null $action
     * @param null $use_base
     * @return $this
     */
    public function loadPartials($action = null, $use_base = null)
    {
        if (is_null($use_base)) {
            $use_base = true;
        }
        if (is_null($action)) {
            $action = $this->getFullActionName('_');
        }
        $this->getLayout()->setAction($action)->load($use_base);

        return $this;
    }

    /**
     * @param null $action
     * @param null $name
     * @param bool $noController
     */
    public function render($action = null, $name = null, $noController = false)
    {

    }

    /**
     * @throws Zend_Controller_Response_Exception
     */
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

    /**
     * @return $this
     * @throws Zend_Exception
     */
    public function oldbrowserAction(): self
    {
        $this->loadPartials('front_index_oldbrowser');
        return $this;
    }

    /**
     *
     */
    public function postDispatch()
    {

        if (!$this->getLayout()->isLoaded() &&
            $this->getRequest()->isDispatched()) {
            $this->_forward('noroute');
        }

        parent::postDispatch();

    }

    /**
     *
     */
    public function norouteAction()
    {

        if (!$this->getRequest()->isApplication()) {
            $this->getResponse()->setHeader('HTTP/1.0', '404 Not Found');
            $this->loadPartials('front_index_noroute');
        } else {
            $this->forward('index', 'index');
        }

    }

    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function forbiddenAction()
    {

        if (!$this->getRequest()->isApplication()) {
            $this->getResponse()
                ->clearHeaders()
                ->setHttpResponseCode(403);

            $this->loadPartials('front_index_forbidden');
        } else {
            $this->forward("index", "index");
        }

    }

    /**
     * @throws Zend_Exception
     */
    public function exceptionAction()
    {

        $errors = $this->_getParam('error_handler');

        $logger = Zend_Registry::get("logger");
        $logger->sendException("Fatal Error: \n" . print_r($errors, true));
    }

    /**
     * @return Siberian_Layout|Siberian_Layout_Email
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getFullActionName($separator = '/')
    {

        return strtolower(join($separator, [
            $this->getRequest()->getModuleName(),
            $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName()
        ]));

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
     * @return array|mixed|string
     */
    public function getPath($uri = '', array $params = [])
    {
        return Core_Model_Url::createPath($uri, $params);
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
     * @throws \Siberian\Exception
     */
    public function downloadAction()
    {

        $path = $this->getRequest()->getParam('path');
        $path = base64_decode($path);

        $name = $this->getRequest()->getParam('name');
        $name = base64_decode($name);

        $content_type = $this->getRequest()->getParam('content_type');

        $this->_download($path, $name, $content_type);

    }

    /**
     * @param $name
     * @param bool $base
     * @return string
     * @throws Zend_Exception
     */
    protected function _getImage($name, $base = false)
    {
        if (file_exists(Core_Model_Directory::getDesignPath(true, '') . '/images/' . $name)) {
            return Core_Model_Directory::getDesignPath($base, '') . '/images/' . $name;
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
    public static function sGetColorizedImage($image_id, $color)
    {

        Siberian_Media::disableTemporary();

        $color = str_replace('#', '', $color);
        $id = md5(implode_polyfill('+', [
            $image_id,
            $color
        ]));
        $url = '';

        $image = new Media_Model_Library_Image();
        if (is_numeric($image_id)) {
            $image->find($image_id);
            if (!$image->getId()) return $url;
            if (!$image->getCanBeColorized()) $color = null;
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path, $image->getAppId());
        } else if (!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
            $path = Core_Model_Directory::getBasePathTo($image_id);
        } else {
            $path = $image_id;
        }

        try {
            $image = new Core_Model_Lib_Image();
            $image->setId($id)
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
     * @param $image_id
     * @param $color
     * @return string
     * @throws Zend_Exception
     */
    protected function _getColorizedImage($image_id, $color): string
    {
        Media::disableTemporary();

        $color = str_replace('#', '', $color ?? "");
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
        } else if (!Zend_Uri::check($image_id) && stripos($image_id, path()) === false) {
            $path = path($image_id);
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
        } catch (\Exception $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * @param string $url
     * @param array $options
     */
    protected function _redirect($url, array $options = [])
    {
        $url = Core_Model_Url::create($url, $options);
        parent::_redirect($url, $options);
    }

    /**
     *
     */
    protected function _initDesign()
    {
        $request = $this->getRequest();
        $detect = new Mobile_Detect();
        $designCode = !$request->isInstalling() ? 'flat' : 'installer';

        $designCodes = [
            'desktop' => $designCode,
        ];

        $white_label_blocks = [
            'flat' => [
                'block' => 'color-blue',
                'color' => 'background_color',
                'color_reverse' => 'color'
            ]
        ];

        Zend_Registry::set('design_codes', $designCodes);
        Siberian_Cache_Design::$design_codes = $designCodes;

        if (!$this->getRequest()->isInstalling()) {
            $apptype = 'desktop';
            $deviceType = 'desktop';
            if ($this->getRequest()->isApplication()) {
                $code = $designCodes['desktop'];
            } else if ($this->_isInstanceOfBackoffice()) {
                $code = 'backoffice';
            } else {
                $code = $designCodes['desktop'];
            }
        } else {
            $apptype = 'desktop';
            $deviceType = 'desktop';
            $code = "installer";
        }

        $base_paths = [APPLICATION_PATH . "/design/email/template/"];

        if (!defined("APPLICATION_TYPE")) define("APPLICATION_TYPE", $apptype);
        if (!defined("DEVICE_TYPE")) define("DEVICE_TYPE", $deviceType);
        if (!defined("DEVICE_IS_IPHONE")) define("DEVICE_IS_IPHONE", $detect->isIphone() || $detect->isIpad());
        if (!defined("IS_APPLICATION")) define("IS_APPLICATION", $detect->isNative() && $this->getRequest()->isApplication());
        if (!defined("DESIGN_CODE")) define("DESIGN_CODE", $code);

        Core_Model_Directory::setDesignPath("/app/sae/design/$apptype/$code");

        $resources = [
            'resources' => [
                'layout' => ['layoutPath' => APPLICATION_PATH . "/design/$apptype/$code/template/page"]
            ]
        ];

        $base_paths[] = APPLICATION_PATH . "/design/$apptype/$code/template/";

        $bootstrap = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $bootstrap->setOptions($resources);

        $bootstrap->bootstrap('View');
        $view = $bootstrap->getResource('View');
        $view->doctype('HTML5');

        foreach ($base_paths as $base_path) {
            $view->addBasePath($base_path);
        }

        Core_View_Default::setDevice($detect);
        Application_Controller_Mobile_Default::setDevice($detect);

        if (!$this->getRequest()->isInstalling()) {
            $blocks = [];
            if ($this->getRequest()->isApplication()) {
                $blocks = $this->getRequest()->getApplication()->getBlocks();
            } else if (!$this->_isInstanceOfBackoffice()) {
                $blocks = $this->getRequest()->getWhiteLabelEditor()->getBlocks();

                if (DESIGN_CODE !== "backoffice" &&
                    $block = $this->getRequest()->getWhiteLabelEditor()->getBlock($white_label_blocks[DESIGN_CODE]["block"])) {
                    $icon_color = $block->getData($white_label_blocks[DESIGN_CODE]["color"]);
                    Application_Model_Option_Value::setEditorIconColor($icon_color);

                    if ($reverse_color = $white_label_blocks[DESIGN_CODE]["color_reverse"]) {
                        Application_Model_Option_Value::setEditorIconReverseColor($block->getData($reverse_color));
                    }
                }
            }

            if (!empty($blocks)) {
                Core_View_Default::setBlocks($blocks);
            }
        }
    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     * @throws Zend_Session_SaveHandler_Exception
     */
    protected function _initSession()
    {
        $request = $this->getRequest();

        // Be sure session is configured correctly
        try {
            if (!$request->isInstalling()) {
                Siberian_Session::init();
            }
        } catch (\Exception $e) {
            // Already init!
        }

        if (!$this->skipSession($request) &&
            !Zend_Session::isStarted() &&
            !$request->isInstalling()) {

            $sbToken = $request->getParam('sb-token', null);
            $xsbAuth = $request->getHeader('XSB_AUTH');

            if (!empty($xsbAuth)) {
                Zend_Session::setId($xsbAuth);
            } else if (!empty($sbToken)) {
                Zend_Session::setId($sbToken);
            }
            // Otherwise, session is already started with the cookie.

            $sessionType = 'front';

            if ($request->isApplication()) {
                if (Siberian_Version::is('sae')) {
                    $sessionType = 'mobile';
                } else {
                    $sessionType = 'mobile' . $request->getApplication()->getAppId();
                }
            } else if ($this->_isInstanceOfBackoffice()) {
                $sessionType = 'backoffice';
            }

            defined('SESSION_TYPE')
            || define('SESSION_TYPE', $sessionType);

            $session = new Core_Model_Session($sessionType);

            // Search if the customer was already logged-in, but the session table was cleared!
            if ($request->isApplication()) {
                $customer = (new Customer_Model_Customer())->find(Zend_Session::getId(), 'session_uuid');
                if ($customer && $customer->getId()) {
                    $session->setCustomer($customer);
                }
            }

            Core_Model_Language::setSession($session);
            Core_View_Default::setSession($session, $sessionType);
            Core_Model_Default::setSession($session, $sessionType);
            self::setSession($session, $sessionType);
        }
    }

    /**
     * @param $request
     */
    public function skipSession ($request)
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION']) &&
            !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return true;
        }

        return false;
    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    protected function _initAcl()
    {
        $request = $this->getRequest();
        $session = $this->getSession();

        if (!$request->isInstalling() &&
            !$request->isApplication() &&
            !$this->_isInstanceOfBackoffice() &&
            $session->isLoggedIn()) {

            $admin = $session->getAdmin();
            $roleId = $admin->getRoleId();
            if (empty($roleId)) {
                $roleId = -1;
            }
            $role = (new Acl_Model_Role())->getRoleById($roleId);

            // If empty roleId, go to login page!
            if (!$role || !$role->getId()) {
                $this->getSession()->resetInstance();
                $this->getSession()->addError(p__('admin', 'Your account has no role assigned to it, please contact your administrator!'));
                $this->_redirect('');
            }
            $acl = new Acl_Model_Acl();
            $acl->prepare($admin);
            Core_View_Default::setAcl($acl);
            Admin_Controller_Default::setAcl($acl);
        }
    }

    /**
     *
     */
    protected function _initLanguage()
    {
        $available_languages = Core_Model_Language::getLanguageCodes();
        $current_language = in_array($this->getRequest()->getLanguageCode(), $available_languages) ? $this->getRequest()->getLanguageCode() : "";
        $language_session = Core_Model_Language::getSession();
        $language = '';

        if (!$this->getRequest()->isApplication()) {
            if ($language_session && $language_session->current_language) {
                $language = $language_session->current_language;
            } else if (!$this->getRequest()->isInstalling()) {
                $current_language = __get('system_default_language');
            }
        } else {
            $language = $language_session->current_language;
        }

        if (!empty($current_language)) {
            Core_Model_Language::setCurrentLanguage($current_language);
        } else if (!empty($language)) {
            // nothing to do!
        } else if ($accepted_languages = Zend_Locale::getBrowser()) {
            $accepted_languages = array_keys($accepted_languages);
            foreach ($accepted_languages as $lang) {
                if (in_array($lang, $available_languages)) {
                    $current_language = $lang;
                    break;
                }
            }

            if (!$current_language) {
                $current_language = Core_Model_Language::getDefaultLanguage();
            }

            Core_Model_Language::setCurrentLanguage($current_language);
        } else {
            Core_Model_Language::setCurrentLanguage(Core_Model_Language::getDefaultLanguage());
        }

        // We also load language values in the translator
        Core_Model_Translator::loadDefaultsAndUser(null, $this->getSession()->getApplication());
    }

    /**
     *
     */
    protected function _initLocale()
    {

        $locale = new Zend_Locale();
        $locale_code = Core_Model_Language::DEFAULT_LOCALE;

        $is_installing = $this->getRequest()->isInstalling();

        if ($this->getRequest()->isApplication() && $this->getApplication()->getLocale()) {
            $locale_code = $this->getApplication()->getLocale();
        } else if (!$is_installing) {

            $currency_code = __get('system_currency');
            if ($currency_code) {
                $currency = new Zend_Currency(null, $currency_code);
                Core_Model_Language::setCurrentCurrency($currency);
            }

            $territory = __get('system_territory');
            if ($territory) {
                $locale_code = $locale->getLocaleToTerritory($territory);
            } else {
                $locale_code = new Zend_Locale(Core_Model_Language::getCurrentLocale());
            }

        }

        if (!$is_installing) {
            $timezone = __get('system_timezone');
            if ($timezone) {
                date_default_timezone_set($timezone);
            }
        }

        $locale->setLocale($locale_code);

        Zend_Registry::set('Zend_Locale', $locale);
    }

    /**
     * @return $this
     */
    protected function _initTranslator()
    {
        return $this;
    }

    /**
     * @return null|string
     */
    protected function _needToBeRedirected()
    {
        $url = null;

        if ($this->getRequest()->isInstalling()) {
            if (!$this->getRequest()->isXmlHttpRequest() AND !in_array($this->getFullActionName('_'), ['front_index_index', 'front_error_error', 'front_error_exception'])) {
                $url = '/';
            }
        }

        if ($this->getRequest()->getLanguageCode()) {
            $url = is_null($url) ? $this->getRequest()->getPathInfo() : $url;

            // filter https? for unwanted redirects
            $url = preg_replace('/(:|%3A)/mi', '', $url);
        }

        return $url;
    }

    /**
     * @deprecated use _sendJson()
     *
     * @param $html
     */
    protected function _sendHtml($html)
    {
        $this->_sendJson($html);
    }

    /**
     * @param $payload
     * @param int $options
     */
    public function _sendJson($payload, $options = JSON_PRETTY_PRINT)
    {
        if (array_key_exists("error", $payload) &&
            $payload["error"] == true) {
            try {
                $this->getResponse()->setHttpResponseCode(400);
            } catch (Exception $e) {
                // Code is valid!
            }
        }

        $json = Siberian_Json::encode($payload, $options);

        Siberian_Debug::sendDataInHeaders();

        $this->getLayout()->setHtml($json);
    }

    /**
     * Download a file or data
     *
     * @param $file
     * @param $filename
     * @param string $content_type
     * @throws \Siberian\Exception
     */
    public function _download($file, $filename, $content_type = 'application/vnd.ms-excel')
    {
        $response = $this->getResponse();

        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-Type', $content_type);
        $response->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Last-Modified', date('r'));

        if (file_exists($file)) {

            $sibTmpPath = Core_Model_Directory::getBasePathTo("/var/tmp");
            $sibAppsPath = Core_Model_Directory::getBasePathTo("/var/apps");
            //check if we download a file from /var/tmp
            if (stripos($file, $sibTmpPath) !== 0 && stripos($file, $sibAppsPath) !== 0) {
                throw new \Siberian\Exception("Forbidden path $file");
            }

            $response->setHeader('Content-Length', filesize($file));
            $response->sendHeaders();

            ob_end_flush();

            readfile($file);
            /** Avoid storing the whole file in memory  */
        } else {
            $response->setHeader('Content-Length', strlen($file));
            $response->setBody($file);
            $response->sendResponse();
        }

        exit();
    }

    /**
     * @param $layout
     * @return $this
     */
    protected function _setBaseLayout($layout)
    {
        $this->_helper->layout()->setLayout($layout);
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isInstanceOfBackoffice()
    {
        return $this instanceof \Backoffice_Controller_Default;
    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction()
    {
        if ($this->getCurrentOptionValue()) {
            $option = $this->getCurrentOptionValue();
            if (Siberian_Exporter::isRegistered($option->getCode())) {
                $class = Siberian_Exporter::getClass($option->getCode());
                $exporter = new $class();
                $result = $exporter->exportAction($option);

                $this->_download($result, $option->getCode() . "-" . date("Y-m-d_h-i-s") . ".yml", "text/x-yaml");
            }
        }
    }

    /**
     * Nothing to do
     */
    public function importAction()
    {
    }


    /**
     * Nothing to do
     */
    public function clearcacheAction()
    {
    }

    /**
     * @param $url
     * @return mixed
     */
    protected function clean_url($url)
    {
        $original_path = parse_url($url, PHP_URL_PATH);
        $path = explode("/", $original_path);
        $new_path = "";
        foreach ($path as $segpath) {
            if ($segpath == "" || $segpath == ".") continue;
            if ($segpath == "..") {
                $new_path = substr($new_path, 0, strrpos($new_path, "/"));
            } else {
                $new_path = $new_path . "/" . $segpath;
            }
        }

        return str_replace($original_path, $new_path, $url);
    }

}
