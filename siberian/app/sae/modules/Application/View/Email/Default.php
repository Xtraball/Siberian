<?php

class Application_View_Email_Default extends Core_View_Email_Default {

    protected $_admin;
    protected $_device;

    public function setAdmin($admin) {
        $this->_admin = $admin;
        return $this;
    }

    public function getAdmin() {
        return $this->_admin;
    }

    public function setDevice($device) {
        $this->_device = $device;
        return $this;
    }

    public function getDevice() {
        return $this->_device;
    }

}
