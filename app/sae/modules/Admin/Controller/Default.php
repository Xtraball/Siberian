<?php

class Admin_Controller_Default extends Core_Controller_Default {

    protected $_admin;
    protected static $_acl;

    public function init() {

        parent::init();

        $this->_admin = $this->getSession()->getAdmin();

        $request = $this->getRequest();


        if($request->getControllerName() == "privacypolicy") {
            return $this;
        }

        if(!$this->getSession()->isLoggedIn()
            AND !preg_match('/(login)|(forgotpassword)|(change)|(map)|(signuppost)|(check)/', $request->getActionName())
            AND !$this->getRequest()->isInstalling()
            ) {
            $this->_forward('login', 'account', 'admin');
            return $this;
        }

        if(!$this->_canAccessCurrentPage()) {
            $this->_forward("forbidden");
            return;
        }

        $this->getSession()->editing_app_id = null;

    }

    public function getAdmin() {
        return $this->_admin;
    }

    public static function setAcl($acl) {
        self::$_acl = $acl;
    }

    protected function _getAcl() {
        return self::$_acl;
    }

    protected function _canAccessCurrentPage() {

        $resource = array(
            "module" => $this->getRequest()->getModuleName(),
            "controller" => $this->getRequest()->getControllerName(),
            "action" => $this->getRequest()->getActionName(),
        );

        return $this->_canAccess($resource);

    }

    protected function _canAccess($resource,$option_value_id = null) {

        if(self::_getAcl()) {
            return self::_getAcl()->isAllowed($resource, $option_value_id);
        }

        return true;
    }
}
