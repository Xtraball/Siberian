<?php

use Siberian\Json;

/**
 * Siberian_Controller_Request_Http
 *
 * HTTP request object for use with Zend_Controller family.
 * Add set & getMediaUrl
 *
 * @uses Zend_Controller_Request_Abstract
 * @package Siberian_Controller
 * @subpackage Request
 */
class Siberian_Controller_Request_Http extends Zend_Controller_Request_Http
{

    /**
     * @var bool
     */
    protected $_is_backoffice = false;
    /**
     * @var bool
     */
    protected $_is_application = false;
    /**
     * @var
     */
    protected $_language_code;
    /**
     * @var bool
     */
    protected $_force_language_code = false;
    /**
     * @var
     */
    protected $_is_native;
    /**
     * @var bool
     */
    protected $_use_application_key = false;
    /**
     * @var
     */
    protected $_white_label_editor;
    /**
     * @var
     */
    public $_application;
    /**
     * @var bool
     */
    protected $_application_key = false;
    /**
     * @var bool
     */
    protected $_ionic_path = false;
    /**
     * @var bool
     */
    protected $_is_installing = false;
    /**
     * @var
     */
    protected $_mediaUrl;
    /**
     * @var
     */
    public $isAllowed;

    /**
     * Siberian_Controller_Request_Http constructor.
     * @param null $uri
     * @throws Zend_Controller_Request_Exception
     * @throws Zend_Exception
     */
    public function __construct($uri = null)
    {
        parent::__construct($uri);
    }

    /**
     * @return array|mixed
     */
    public function getBodyParams()
    {
        $rawBody = $this->getRawBody();

        return Json::decode($rawBody);
    }

    /**
     * @param $rawBody
     * @return $this
     */
    public function setRawBody($rawBody)
    {
        $this->_rawBody = $rawBody;

        return $this;
    }

    /**
     * @param null $pathInfo
     * @return $this|Zend_Controller_Request_Http
     * @throws Zend_Exception
     */
    public function setPathInfo($pathInfo = null)
    {

        parent::setPathInfo($pathInfo);

        $path = $this->_pathInfo;
        $paths = explode('/', trim($path, '/'));

        $paths = $this->__findLanguage($paths);

        if (!$this->isInstalling()) {

            $paths = $this->_initApplication($paths);

            if (!$this->isApplication()) {
                $this->_initWhiteLabelEditor();
            }

        }

        $paths = array_values($paths);
        $this->_pathInfo = '/' . implode_polyfill('/', $paths);

        $detector = new Mobile_Detect();
        $this->_is_native = $detector->isNative();

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setMediaUrl($url)
    {
        $this->_mediaUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        $url = $this->_mediaUrl;
        if (!$url) {
            $url = $this->_baseUrl;
        }

        return $url;
    }

    /**
     * @return mixed
     */
    public function getLanguageCode()
    {
        return $this->_language_code;
    }

    /**
     * @param $language_code
     * @return $this
     */
    public function setLanguageCode($language_code): self
    {
        $this->_language_code = $language_code;
        return $this;
    }

    /**
     * @param null $language_code
     * @return $this|bool
     */
    public function addLanguageCode($language_code = null)
    {
        if ($language_code !== null) {
            $this->_force_language_code = true;
            $this->_language_code = $language_code;
            return $this;
        }
        return $this->_force_language_code;
    }

    /**
     * @return bool
     */
    public function isApplication(): bool
    {
        /** @migration sae/mae/pe */
        if (Siberian_Version::is('SAE')) {
            return $this->_is_application;
        }
        return $this->_application !== null;
    }

    /**
     * @return bool
     */
    public function isBackoffice()
    {
        return $this->_is_backoffice;
    }

    /**
     * @return bool
     */
    public function isWhiteLabelEditor()
    {
        return $this->getWhiteLabelEditor() && $this->getWhiteLabelEditor()->isActive();
    }

    /**
     * @return mixed
     */
    public function isNative()
    {
        return $this->_is_native;
    }

    /**
     * @return Application_Model_Application
     * @throws Zend_Exception
     */
    public function getApplication()
    {
        /** @migration sae/mae/pe */
        if (Siberian_Version::is('SAE')) {
            return Application_Model_Application::getInstance();
        }
        return $this->_application;
    }

    /**
     * @return mixed
     */
    public function getWhiteLabelEditor()
    {
        return $this->_white_label_editor;
    }

    /**
     * @param null $use_key
     * @return $this|bool
     */
    public function useApplicationKey($use_key = null)
    {
        /** @migration sae/mae/pe */
        if (Siberian_Version::is('SAE')) {
            if (is_bool($use_key)) {
                $this->_use_application_key = $use_key;
                return $this;
            }

            return $this->_use_application_key;
        }
        return (bool)$this->_application_key;
    }

    /**
     * @return bool
     */
    public function getApplicationKey()
    {
        return $this->_application_key;
    }

    /**
     * @param $app_key
     */
    public function setApplicationKey($app_key)
    {
        $this->_application_key = $app_key;
    }

    /**
     * @param null $ionic_path
     * @return $this
     */
    public function setIonicPath($ionic_path = null)
    {
        $this->_ionic_path = $ionic_path;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIonicPath()
    {
        return $this->_ionic_path;
    }

    /**
     * @return bool
     */
    public function useIonicPath()
    {
        return !!$this->_ionic_path;
    }

    /**
     * @param $isAllowed
     */
    public function setIsAllowed($isAllowed)
    {
        $this->isAllowed = $isAllowed;
    }

    /**
     * @return mixed
     */
    public function getIsAllowed()
    {
        return $this->isAllowed;
    }

    /**
     * @param null $isInstalling
     * @return $this|bool
     */
    public function isInstalling($isInstalling = null)
    {
        if ($isInstalling !== null) {
            $this->_is_installing = $isInstalling;
            return $this;
        }
        return $this->_is_installing;
    }

    /**
     * @return array
     */
    public function getFilteredParams(): array
    {
        $params = $this->getParams();
        $replacements = ['module', 'controller', 'action'];

        foreach ($replacements as $replacement) {
            if (isset($params[$replacement])) {
                unset($params[$replacement]);
            }
        }

        return $params;
    }

    /**
     * @param $paths
     * @return mixed
     * @throws Zend_Exception
     */
    protected function _initApplication($paths)
    {
        $application = new Application_Model_Application();
        $application->find($this->getHttpHost(), 'domain');

        /** @migration sae/mae/pe */
        if (Siberian_Version::is('SAE')) {
            if ($application->getId()) {
                $this->_is_application = true;
                $this->_use_application_key = false;
            }

            if ((!$application->getId() || $application->useIonicDesign()) &&
                !empty($paths[0]) &&
                $paths[0] == Application_Model_Application::OVERVIEW_PATH) {
                $this->_is_application = true;
                $this->_use_application_key = true;
                unset($paths[0]);
            }
        } else {
            if ($application->getId()) {
                $this->_application = $application;
                $this->_application_key = false;
            }

            if ((!$application->getId() || $application->useIonicDesign()) &&
                !empty($paths[0])) {
                $application->find($paths[0], "key");
                if ($application->getId()) {
                    $this->_application = $application;
                    $this->_application_key = $application->getKey();
                    unset($paths[0]);
                }

            }
        }

        // Init whitelabel for apps
        if ($this->_application &&
            Siberian_Version::is('PE')) {
            if (!$this->white_label_editor ||
                !$this->_white_label_editor->isActive()) {
                // try to get whitelabel from admins
                $admin = new Admin_Model_Admin();
                $admin->find($this->_application->getAdminId());
                $parent = $admin->getParentId();
                if ($parent) {
                    $admin->unsData();
                    $admin->find($parent);
                }
                if ($admin->getId()) {
                    $this->_white_label_editor = new Whitelabel_Model_Editor();
                    $this->_white_label_editor->find($admin->getId(), 'admin_id');
                    if (!$this->_white_label_editor->isActive()) {
                        $this->_white_label_editor->unsData();
                    }
                }
            }
        }

        return $paths;
    }

    /**
     * @return $this
     */
    protected function _initWhiteLabelEditor()
    {
        try {
            if (Installer_Model_Installer::hasModule('whitelabel')) {
                $this->_white_label_editor = new Whitelabel_Model_Editor();
                $this->_white_label_editor->find($this->getHttpHost(), 'host');
                if (!$this->_white_label_editor->isActive()) {
                    $this->_white_label_editor->unsData();
                }
            } else {
                $this->_white_label_editor = new Core_Model_Default(['is_active' => false]);
            }
        } catch (Exception $e) {
            // Silent exception!
        }

        return $this;
    }

    /**
     * @param $paths
     * @return array
     */
    private function __findLanguage($paths)
    {
        $language = !empty($paths[0]) ? $paths[0] : '';

        if (in_array($language, Core_Model_Language::getLanguageCodes())) {
            $this->_language_code = $language;
            unset($paths[0]);
            $paths = array_values($paths);
        }

        return $paths;
    }

}
