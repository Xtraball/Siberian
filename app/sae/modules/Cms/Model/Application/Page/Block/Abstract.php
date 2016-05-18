<?php

abstract class Cms_Model_Application_Page_Block_Abstract extends Core_Model_Default {

    public abstract function isValid();

    public function getImageUrl() {
        return $this->getImage() ? Application_Model_Application::getImagePath().$this->getImage() : null;
    }
}