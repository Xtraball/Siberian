<?php

class Admin_View_Default extends Front_View_Index_Index
{

    protected $_admin;
    protected $_pos;

    public function __construct($config = array()) {
        $this->_admin = $this->getSession()->getAdmin();
        parent::__construct($config);
    }

    public function setAdmin($admin) {
        $this->_admin = $admin;
        return $this;
    }
    
    public function getAdmin() {
        return $this->_admin;
    }

}