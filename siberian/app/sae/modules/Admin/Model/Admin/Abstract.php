<?php

abstract class Admin_Model_Admin_Abstract extends Core_Model_Default
{

    protected $_applications;
    protected $_subaccounts;
    protected $_white_label_editor;

    const LOGO_PATH = '/images/admin';
    const BO_DISPLAYED_PER_PAGE = 500;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Admin_Model_Db_Table_Admin';
    }

    /**
     * Get only pusblished applications
     *
     * @return mixed
     */
    public function getPublishedApplicationsForAdmin() {
        $application_table = new Application_Model_Db_Table_Application();
        $applications = $application_table->findAllForGlobalPush();

        if(count($applications) <= 0) {
            return array();
        }

        $application_model = new Application_Model_Application();
        $admin_applications = $application_model->findAllByAdmin($this->getId(), array(
            "a.app_id IN (?)" => $applications
        ));

        return $admin_applications;
    }

    public function findByEmail($email) {
        return $this->find($email, 'email');
    }

    public function getStats() {
        return $this->getTable()->getStats();
    }

    public function save() {

        $countries = Zend_Registry::get('Zend_Locale')->getTranslationList('Territory', null, 2);

        if($this->getCountryCode()) {
            if(empty($countries[$this->getCountryCode()])) {
                throw new Exception(__("An error occurred while saving. The country is not valid."));
            } else if($this->getCountry() != $countries[$this->getCountryCode()]) {
                $this->setCountry($countries[$this->getCountryCode()]);
            }
        }

        return parent::save();

    }

    public function getApplications() {

        if(!$this->_applications) {
            $this->_applications = array(Application_Model_Application::getInstance());
        }

        return $this->_applications;
    }

    public function getApplicationsByDesignType($type) {

        if($type) {
            return $this->getTable()->getApplicationsByDesignType($type, $this->getId());
        }

        return array();
    }

    public function getAllApplicationAdmins($app_id) {
        if($app_id) {
            return $this->getTable()->getAllApplicationAdmins($app_id);
        }
        return array();
    }

    public function getLoginToken() {
        return md5($this->getFirstname().$this->getEmail().$this->getId());
    }

    public function isAllowedToAddPages($app_id = null) {
        return (bool) $this->getIsAllowedToAddPages();
    }

    public function getWhiteLabelEditor() {

        if(!$this->_white_label_editor) {

            if(Installer_Model_Installer::hasModule("whitelabel")) {
                $this->_white_label_editor = new Whitelabel_Model_Editor();
                $this->_white_label_editor->find($this->getId(), "admin_id");
            } else {
                $this->_white_label_editor = new Core_Model_Default();
            }
        }

        return $this->_white_label_editor;
    }

    public function canPublishThemself() {

        $publication_type = System_Model_Config::getValueFor("system_publication_access_type");

        $admin = new Admin_Model_Admin();
        $admin->find($this->getApplication()->getAdminId());

        $publish_rights = $admin->getPublicationAccessType() ? $admin->getPublicationAccessType() : $publication_type;

        return $publish_rights == 'sources';

    }

    /**
     * Check if admin can generate apk, fallback with global param if not set.
     *
     * @return bool
     */
    public function canGenerateApk() {

        $global_generate_apk = System_Model_Config::getValueFor("system_generate_apk");

        $admin = new Admin_Model_Admin();
        $admin->find($this->getApplication()->getAdminId());

        $admin_generate_apk = $admin->getGenerateApk() ? $admin->getGenerateApk() : $global_generate_apk;

        return ($admin_generate_apk == 'yes');

    }

    public function setPassword($password) {
        if (strlen($password) < 6) {
            throw new Siberian_Exception(__('The password must be at least 6 characters'));
        }
        $this->setData('password', $this->_encrypt($password));
        return $this;
    }

    public function isSamePassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

    public function authenticate($password) {
        return $this->_checkPassword($password);
    }

    public static function getLogoPathTo($path = '') {
        return Core_Model_Directory::getPathTo(self::LOGO_PATH.$path);
    }

    public static function getBaseLogoPathTo($path = '') {
        return Core_Model_Directory::getBasePathTo(self::LOGO_PATH.$path);
    }

    public static function getNoLogo($base = false) {
        return $base ? self::getBaseLogoPathTo('placeholder/no-logo.png') : self::getLogoPathTo('placeholder/no-logo.png');
    }

    public function getLogoLink() {
        if($this->getData('logo') AND is_file(self::getBaseLogoPathTo($this->getData('logo')))) {
            return self::getLogoPath($this->getData('logo'));
        }
        else {
            return self::getNoLogo();
        }

    }

    public function getLogoUrl() {
        return $this->getBaseUrl().$this->getLogoLink();
    }

    public function getBaseLogoLink() {
        if($this->getData('logo') AND is_file(self::getBaseLogoPathTo($this->getLogo()))) return self::getBaseLogoPathTo($this->getLogo());
        else return self::getNoLogo(true);
    }

    public function getAvailableRole() {
        $roles = $this->getTable()->getAvailableRole();

        for($i = 0; $i<count($roles); $i++) {
            $roles[$i]["label"] = __($roles[$i]["label"]);
            $roles[$i]["code"] = __($roles[$i]["code"]);
        }
        return $roles;
    }

    public function sendCreationAccountEmail($password) {

        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->getLayoutInstance()
            ->loadEmail('admin', 'create_account');
        $layout->getPartial('content_email')->setAdmin($this)->setPassword($password);
        $content = $layout->render();

        $sender = System_Model_Config::getValueFor("support_email");
        $support_name = System_Model_Config::getValueFor("support_name");

        # @todo SMTP
        # @version 4.8.7 - SMTP
        if($sender AND $support_name) {
            //Mail to new client
            $mail = new Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->setFrom($sender, $support_name);
            $mail->addTo($this->getEmail());
            $mail->setSubject(__("Welcome!"));
            $mail->send();

            //mail to admin
            $end_message = System_Model_Config::getValueFor("signup_mode") == "validation" ? " ".__("Connect to your backoffice to validate this account.") : "";
            $mail = new Siberian_Mail();
            $mail->setBodyHtml(__("Hello, a new user has registered on your platform : %s.", $this->getEmail()).$end_message);
            $mail->setFrom($sender, $support_name);
            $mail->addTo($sender);
            $mail->setSubject(__("New user registration on your platform"));
            $mail->send();
        }

        return $this;

    }

    private function _encrypt($password) {
        return sha1($password);
    }

    private function _checkPassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

}