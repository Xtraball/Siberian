<?php

class Cms_Model_Application_Page_Block_Button extends Cms_Model_Application_Page_Block_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Button';
        return $this;
    }
    
    public function isValid() {

        if($this->getContent()) {
            if($this->getTypeId() == "link") {
                
            }

            return true;
        }

        if($this->getContent()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {

        $this->setTypeId($data["type"]);
        $this->setLabel($data["label"]);
        $this->setHideNavbar($data["hide_navbar"]);
        $this->setUseExternalApp($data["use_external_app"]);

        $icon = Siberian_Feature::saveImageForOptionDelete($this->option_value, $data["icon"]);

        $this->setIcon($icon);

        switch($data["type"]) {
            case "phone":
                    $this->setContent($data["phone"]);
                break;
            case "link":
                    $this->setContent($data["link"]);
                break;
            case "email":
                    $this->setContent($data["email"]);
                break;
        }

        return $this;
    }
    
}