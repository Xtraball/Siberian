<?php
class Weblink_Model_Weblink_Link extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Weblink_Model_Db_Table_Weblink_Link';
        return $this;
    }

    public function getUrl() {
        return $this->getData('url');
    }

    public function getHideNavbar() {
        return ($this->getData('hide_navbar') === "1" ? true : false);
    }

    public function getUseExternalApp() {
        return ($this->getData('use_external_app') === "1" ? true : false);
    }

    public function getPictoUrl() {
        $picto_path = Application_Model_Application::getImagePath().$this->getPicto();
        $picto_base_path = Application_Model_Application::getBaseImagePath().$this->getPicto();
        if($this->getPicto() AND file_exists($picto_base_path)) {
            return $picto_path;
        }
        return null;
    }

    public function __toString() {
        parent::__toString();
        return $this->getUrl() ? $this->getUrl() : '';
    }

}
