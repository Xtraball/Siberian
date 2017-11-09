<?php

class Application_Model_Layout_Homepage extends Application_Model_Layout_Abstract {

    const VISIBILITY_HOMEPAGE = "homepage";
    const VISIBILITY_ALWAYS = "always";
    const VISIBILITY_TOGGLE = "toggle";
    const VISIBILITY_FULLSCREEN = "fullscreen";

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Layout_Homepage';

        /** Default category is custom, the default is only for system layouts. */
        $layout_category = new Application_Model_Layout_Category();
        $custom_layout_category = $layout_category->find("custom", "code");
        $this->setCategoryId($custom_layout_category->getId());

        return $this;
    }

    public function getNumberOfDisplayedIcons() {
        return (int) $this->getData('number_of_displayed_icons');
    }

    public function isActive() {
        return $this->getData("is_active");
    }

}
