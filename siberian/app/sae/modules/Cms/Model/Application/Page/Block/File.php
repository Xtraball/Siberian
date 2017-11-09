<?php

/**
 * Class Cms_Model_Application_Page_Block_File
 */
class Cms_Model_Application_Page_Block_File extends Cms_Model_Application_Page_Block_Abstract {

    /**
     * Cms_Model_Application_Page_Block_File constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_File';
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        if($this->getName()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {
        $file = Siberian_Feature::saveFileForOption($this->option_value, $data["file"]);

        $this->setLabel($data["label"]);
        $this->setOriginalName($data["original_name"]);
        $this->setName($file);

        return $this;
    }

}