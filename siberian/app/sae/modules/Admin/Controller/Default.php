<?php

use Siberian\Security;

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

        // Guest routes (doesn't require active auth)
        $allowed = Security::$routesGuest;
        if ($request->getControllerName() === 'privacypolicy' ||
            in_array($this->getFullActionName('_'), $allowed, false)) {
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
            && !preg_match('/(login)|(forgotpassword)|(change)|(map)|(signuppost)|(check)/', $request->getActionName())
            && !$this->getRequest()->isInstalling()
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
     * @return bool
     * @throws Zend_Session_Exception
     */
    protected function _canAccessCurrentPage()
    {
        $request = $this->getRequest();

        $resource = [
            "module" => $request->getModuleName(),
            "controller" => $request->getControllerName(),
            "action" => $request->getActionName(),
        ];


        // Searching for any optionValue, this never prevented options to be edited
        $valueId = null;
        if ($request->getParam("option_value_id", false) !== false) {
            $valueId = $request->getParam("option_value_id", false);
        } else if ($request->getParam("value_id", false) !== false) {
            $valueId = $request->getParam("value_id", false);
        }

        if ($valueId !== null) {
            // Checking for Application ACL
            $applicationAcl = (new Application_Model_Acl_Option())
                ->find([
                    "admin_id" => $this->getSession()->getAdminId(),
                    "value_id" => $valueId,
                ]);

            if ($applicationAcl->getId()) {
                return false;
            }

            return $this->_canAccess($resource, $valueId);
        }

        return $this->_canAccess($resource);
    }

    /**
     * @param $resource
     * @param null $option_value_id
     * @return bool
     */
    protected function _canAccess($resource, $option_value_id = null)
    {

        if (self::_sGetAcl()) {
            return self::_sGetAcl()->isAllowed($resource, $option_value_id);
        }

        return true;
    }
}
