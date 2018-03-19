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
    public $secured_actions = [];

    /**
     * Bearer token
     * Note: special implementation with custom header
     *       on some servers HTTP Authorization never reaches PHP (FastCGI, etc...)
     *
     * @var string
     */
    public $bearerAuthHeaderKey = 'Api-Auth-Bearer';

    /**
     * @return $this
     */
    public function init() {
    
        parent::init();

        $request = $this->getRequest();

        // Test AUTH
        if (!preg_match("/admin_api_account_autologin/", $this->getFullActionName("_"))) {

            // Special case grant auth, this AUTH grants ALL API Privileges to the localhost
            if (($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']) &&
                (__getConfig('allow_local_api') === true)) {
                return $this;
            }

            // Bearer Auth
            $bearer = $request->getHeader($this->bearerAuthHeaderKey);
            if (!empty($bearer) && (strpos($bearer, 'Bearer') === 0)) {
                $authBearer = trim(str_replace('Bearer', '', $bearer));

                $this->user = (new Api_Model_User())
                    ->find($authBearer, 'bearer_token');

                // If there is no such user, then redirect!
                if (!$this->user->getId()) {
                    return $this->forward('notauthorized');
                }
            } else { // Basic Auth
                $username = $request->getServer('PHP_AUTH_USER');
                $password = $request->getServer('PHP_AUTH_PW');

                $this->user = (new Api_Model_User())
                    ->find($username, 'username');

                // If there is no such user, then redirect!
                if (!$this->user->getId() || !$this->user->authenticate($password)) {
                    return $this->forward('notauthorized');
                }
            }
        }

        // Test ACL
        if (in_array($request->getActionName(), $this->secured_actions)) {
            return $this->hasAccess();
        }

        return $this;

    }

    /**
     * @param null $key
     * @return $this
     */
    public function hasAccess($key = null) {
        if (empty($key)) {
            $key = sprintf("%s.%s", $this->namespace, $this->getRequest()->getActionName());
        }

        if (!$this->user->hasAccess($key)) {
            return $this->forward('notauthorized');
        }

        return $this;
    }

    /**
     *
     */
    public function notauthorizedAction() {
        $payload = [
            'error' => true,
            'message' => __('Authentication failed. Please, check the username and/or the password')
        ];
        $this->_sendJson($payload);
    }
}
