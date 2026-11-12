<?php

class Application_View_Email_Default extends Core_View_Email_Default {

    protected $_admin;

    public function setAdmin($admin) {
        $this->_admin = $admin;
        return $this;
    }

    public function getAdmin() {
        return $this->_admin;
    }

}
