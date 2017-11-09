<?php

class Core_Model_Session_Instance_Abstract
{

    protected $_object;

    public function getObject() {
        return $this->_object;
    }

    public function setObject($object) {
        $this->_object = $object;
        return $this;
    }

    public function isLoggedIn() {
        return false;
    }

}
