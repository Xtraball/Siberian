<?php

class Front_View_Html_Messages extends Core_View_Default
{

    protected $_messages;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_messages = $this->getSession()->getMessages();
    }

    public function canView() {
        return $this->hasSuccess() || $this->hasWarning() || $this->hasError();
    }

    public function hasSuccess() {
        return count($this->getMessages()->getSuccess()) > 0;
    }

    public function hasWarning() {
        return count($this->getMessages()->getWarning()) > 0;
    }

    public function hasError() {
        return count($this->getMessages()->getError()) > 0;
    }

    public function getSuccess() {
        return $this->getMessages()->getSuccess();
    }

    public function getWarning() {
        return $this->getMessages()->getWarning();
    }

    public function getError() {
        return $this->getMessages()->getError();
    }

    public function getMessages() {
        return $this->_messages;
    }

}