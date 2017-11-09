<?php

class Api_Controller_Default extends Core_Controller_Default {

    /**
     * @var string
     */
    public $namespace = "api";

    /**
     * @var Api_Model_User
     */
    public $user = null;

    /**
     * @var array
     */
    public $secured_actions = array();

    /**
     * @return $this
     */
    public function init() {
    
        parent::init();

        # Test AUTH
        if(!preg_match("/admin_api_account_autologin/", $this->getFullActionName("_"))) {
            
            $username = $this->getRequest()->getServer("PHP_AUTH_USER");
            $password = $this->getRequest()->getServer("PHP_AUTH_PW");

            $this->user = new Api_Model_User();
            $this->user->find($username, "username");
            if(!$this->user->getId() OR !$this->user->authenticate($password)) {
                return $this->forward("notauthorized");
            }

        }

        # Test ACL
        if(in_array($this->getRequest()->getActionName(), $this->secured_actions)) {
            return $this->hasAccess();
        }

        return $this;

    }

    /**
     * @param null $key
     * @return $this|void
     */
    public function hasAccess($key = null) {
        if(empty($key)) {
            $key = sprintf("%s.%s", $this->namespace, $this->getRequest()->getActionName());
        }

        if(!$this->user->hasAccess($key)) {
            return $this->forward("notauthorized");
        }

        return $this;
    }

    /**
     *
     */
    public function notauthorizedAction() {
        $data = array(
            "error" => 1,
            "message" => $this->_("Authentication failed. Please, check the username and/or the password")
        );
        $this->_sendHtml($data);
    }

}
