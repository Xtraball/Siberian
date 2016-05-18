<?php

class Application_Model_Layout_Homepage extends Application_Model_Layout_Abstract {

    const VISIBILITY_HOMEPAGE = "homepage";
    const VISIBILITY_ALWAYS = "always";
    const VISIBILITY_TOGGLE = "toggle";

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Layout_Homepage';
        return $this;
    }

    public function getNumberOfDisplayedIcons() {
        return (int) $this->getData('number_of_displayed_icons');
    }

    public function isActive() {
        return $this->getData("is_active");
        return $this->getId() <= 7 || file_exists(Core_Model_Directory::getDesignPath(true, "template/home/l{$this->getId()}/view.phtml", "mobile"));
    }

}
