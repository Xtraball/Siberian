<?php

/**
 * Class Admin_Controller_Default
 */
class Admin_Controller_Default extends Core_Controller_Default
{

    /**
     * @var
     */
    protected $_admin;
    /**
     * @var
     */
    protected static $_acl;

    /**
     * @var array
     */
    public $openActions = [];

    /**
     * @return $this|void
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     * @throws \Siberian\Exception
     */
    public function init()
    {
        parent::init();

        $this->_admin = $this->getSession()->getAdmin();

        $request = $this->getRequest();

        if ($request->getControllerName() == "privacypolicy") {
            return $this;
        }

        foreach ($this->openActions as $openAction) {
            if ($request->getModuleName() === $openAction['module'] &&
                $request->getControllerName() === $openAction['controller'] &&
                $request->getActionName() === $openAction['action']) {
                return $this;
            }
        }

        if (!$this->getSession()->isLoggedIn()
            AND !preg_match('/(login)|(forgotpassword)|(change)|(map)|(signuppost)|(check)/', $request->getActionName())
            AND !$this->getRequest()->isInstalling()
        ) {
            $this->_forward('login', 'account', 'admin');
            return $this;
        }

        if (!$this->_canAccessCurrentPage()) {
            $this->_forward("forbidden");
            return;
        }

        $this->getSession()->editing_app_id = null;

    }

    /**
     * @return mixed
     */
    public function getAdmin()
    {
        return $this->_admin;
    }

    /**
     * @param $acl
     */
    public static function setAcl($acl)
    {
        self::$_acl = $acl;
    }

    /**
     * @return mixed
     */
    protected function _getAcl()
    {
        return self::$_acl;
    }

    /**
     * @return bool
     */
    protected function _canAccessCurrentPage()
    {

        $resource = array(
            "module" => $this->getRequest()->getModuleName(),
            "controller" => $this->getRequest()->getControllerName(),
            "action" => $this->getRequest()->getActionName(),
        );

        return $this->_canAccess($resource);

    }

    /**
     * @param $resource
     * @param null $option_value_id
     * @return bool
     */
    protected function _canAccess($resource, $option_value_id = null)
    {

        if (self::_getAcl()) {
            return self::_getAcl()->isAllowed($resource, $option_value_id);
        }

        return true;
    }
}
