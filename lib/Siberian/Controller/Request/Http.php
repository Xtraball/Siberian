<?php
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

    protected $_is_backoffice = false;
    protected $_is_application = false;
    protected $_language_code;
    protected $_force_language_code = false;
    protected $_is_native;
    protected $_use_application_key = false;
    protected $_white_label_editor;
    public $_application;
    protected $_application_key = false;
    protected $_ionic_path = false;
    protected $_is_installing = false;
    protected $_mediaUrl;

    public function setPathInfo($pathInfo = null) {

        parent::setPathInfo($pathInfo);

        $path = $this->_pathInfo;
        $paths = explode('/', trim($path, '/'));

        $paths = $this->__findLanguage($paths);

        if(!$this->isInstalling()) {

            $paths = $this->_initApplication($paths);

            if(!$this->isApplication()) {
                $this->_initWhiteLabelEditor();
            }

        }

        $paths = array_values($paths);
        $this->_pathInfo = '/'.implode('/', $paths);

        $detector = new Mobile_Detect();
        $this->_is_native = $detector->isNative();

        return $this;
    }

    public function setMediaUrl($url) {
        $this->_mediaUrl = $url;
        return $this;
    }

    public function getMediaUrl() {
        $url = $this->_mediaUrl;
        if(!$url) {
            $url = $this->_baseUrl;
        }

        return $url;
    }

    public function getLanguageCode() {
        return $this->_language_code;
    }

    public function setLanguageCode($language_code) {
        $this->_language_code = $language_code;
        return $this;
    }

    public function addLanguageCode($language_code = null) {
        if(!is_null($language_code)) {
            $this->_force_language_code = true;
            $this->_language_code = $language_code;
            return $this;
        } else {
            return $this->_force_language_code;
        }
    }

    public function isApplication() {
        /** @migration sae/mae/pe */
        if(Siberian_Version::is("SAE")) {
            return $this->_is_application;
        } else {
            return !is_null($this->_application);
        }
    }

    public function isBackoffice() {
        return $this->_is_backoffice;
    }

    public function isWhiteLabelEditor() {
        return $this->getWhiteLabelEditor() && $this->getWhiteLabelEditor()->isActive();
    }

    public function isNative() {
        return $this->_is_native;
    }

    public function getApplication() {
        /** @migration sae/mae/pe */
        if(Siberian_Version::is("SAE")) {
            return Application_Model_Application::getInstance();
        } else {
            return $this->_application;
        }
    }

    public function getWhiteLabelEditor() {
        return $this->_white_label_editor;
    }

    public function useApplicationKey($use_key = null) {
        /** @migration sae/mae/pe */
        if(Siberian_Version::is("SAE")) {
            if (is_bool($use_key)) {
                $this->_use_application_key = $use_key;
                return $this;
            }

            return $this->_use_application_key;
        } else {
            return (bool) $this->_application_key;
        }

    }

    public function getApplicationKey() {
        return $this->_application_key;
    }

    public function setApplicationKey($app_key) {
        $this->_application_key = $app_key;
    }

    public function setIonicPath($ionic_path = null) {
        $this->_ionic_path = $ionic_path;
        return $this;
    }

    public function getIonicPath() {
        return $this->_ionic_path;
    }

    public function useIonicPath() {
        return !!$this->_ionic_path;
    }

    public function isInstalling($isInstalling = null) {
        if(!is_null($isInstalling)) {
            $this->_is_installing = $isInstalling;
            return $this;
        } else {
            return $this->_is_installing;
        }
    }

    public function getFilteredParams() {

        $params = $this->getParams();
        $replacements = array("module", "controller", "action");

        foreach($replacements as $replacement) {
            if(isset($params[$replacement])) unset($params[$replacement]);
        }

        return $params;

    }

    protected function _initApplication($paths) {

        $application = new Application_Model_Application();
        $application->find($this->getHttpHost(), "domain");

        /** @migration sae/mae/pe */
        if(Siberian_Version::is("SAE")) {
            if ($application->getId()) {
                $this->_is_application = true;
                $this->_use_application_key = false;
            }

            if((!$application->getId() || $application->useIonicDesign()) AND !empty($paths[0]) AND $paths[0] == Application_Model_Application::OVERVIEW_PATH) {
                $this->_is_application = true;
                $this->_use_application_key = true;
                unset($paths[0]);
            }
        } else {
            if ($application->getId()) {
                $this->_application = $application;
                $this->_application_key = false;
            }

            if((!$application->getId() || $application->useIonicDesign()) AND !empty($paths[0])) {
                $application->find($paths[0], "key");
                if($application->getId()) {
                    $this->_application = $application;
                    $this->_application_key = $application->getKey();
                    unset($paths[0]);
                }

            }
        }

        // Init whitelabel for apps
        if($this->_application && Siberian_Version::is("PE")) {
            if(!$this->white_label_editor || !$this->_white_label_editor->isActive()) {
                // try to get whitelabel from admins
                $admin = new Admin_Model_Admin();
                $admin->find($this->_application->getAdminId());
                $parent = $admin->getParentId();
                if($parent) {
                    $admin->unsData();
                    $admin->find($parent);
                }
                if($admin->getId()) {
                    $this->_white_label_editor = new Whitelabel_Model_Editor();
                    $this->_white_label_editor->find($admin->getId(), "admin_id");
                    if(!$this->_white_label_editor->isActive()) {
                        $this->_white_label_editor->unsData();
                    }
                }
            }
        }

        return $paths;

    }

    protected function _initWhiteLabelEditor() {

        try {
            if(Installer_Model_Installer::hasModule("whitelabel")) {
                $this->_white_label_editor = new Whitelabel_Model_Editor();
                $this->_white_label_editor->find($this->getHttpHost(), "host");
                if(!$this->_white_label_editor->isActive()) {
                    $this->_white_label_editor->unsData();
                }
            } else {
                $this->_white_label_editor = new Core_Model_Default(array("is_active" => false));
            }

        } catch(Exception $e) {}

        return $this;

    }

    private function __findLanguage($paths) {

        $language = !empty($paths[0]) ? $paths[0] : '';

        if(in_array($language, Core_Model_Language::getLanguageCodes())) {
            $this->_language_code = $language;
            unset($paths[0]);
            $paths = array_values($paths);
        }

        return $paths;

    }

}
