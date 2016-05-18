<?php

class Translation_View_Default extends Core_View_Default
{

    protected $_admin;
    protected $_pos;

    public function __construct($config = array()) {
        $this->_admin = $this->getSession()->getAdmin();
        parent::__construct($config);
    }

    public function getAdmin() {
        return $this->_admin;
    }

}