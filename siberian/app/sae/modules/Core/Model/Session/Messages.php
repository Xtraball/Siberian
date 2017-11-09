<?php

class Core_Model_Session_Messages extends Core_Model_Default
{

    protected $_success = array();
    protected $_warning = array();
    protected $_error = array();

    public function addSuccess($msg, $key) {
        if(empty($key)) $key = count($this->_success);
        $this->_success[$key] = $msg;
        return $this;
    }

    public function removeSuccess($key) {
        if(isset($this->_success[$key])) unset($this->_success[$key]);
        return $this;
    }

    public function addWarning($msg, $key) {
        if(empty($key)) $key = count($this->_warning);
        $this->_warning[$key] = $msg;
        return $this;
    }

    public function removeWarning($key) {
        if(isset($this->_warning[$key])) unset($this->_warning[$key]);
        return $this;
    }

    public function addError($msg, $key) {
        if(empty($key)) $key = count($this->_error);
        $this->_error[$key] = $msg;
        return $this;
    }

    public function removeError($key) {
        if(isset($this->_error[$key])) unset($this->_error[$key]);
        return $this;
    }

    public function getSuccess() {
        return $this->_success;
    }

    public function getWarning() {
        return $this->_warning;
    }

    public function getError() {
        return $this->_error;
    }

}
