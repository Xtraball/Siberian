<?php

class Backoffice_Model_Session extends Core_Model_Session_Instance_Abstract
{

    public function __construct($params) {
        $admin = new Backoffice_Model_User();
        $admin->find($params['id']);
        $this->setObject($admin);
    }

    public function isLoggedIn() {
        return $this->getObject() && $this->getObject()->getId();
    }
}