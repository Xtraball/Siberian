<?php

/**
 * Class Admin_View_Default
 */
class Admin_View_Default extends Front_View_Index_Index {

    /**
     * @var Admin_Model_Admin
     */
    protected $_admin;

    /**
     * @var Integer
     */
    protected $_pos;

    /**
     * Admin_View_Default constructor.
     * @param array $config
     */
    public function __construct($config = []) {
        $this->_admin = $this->getSession()->getAdmin();
        parent::__construct($config);
    }

    /**
     * @param Admin_Model_Admin $admin
     * @return $this
     */
    public function setAdmin($admin) {
        $this->_admin = $admin;
        return $this;
    }

    /**
     * @return Admin_Model_Admin
     */
    public function getAdmin() {
        return $this->_admin;
    }
}
