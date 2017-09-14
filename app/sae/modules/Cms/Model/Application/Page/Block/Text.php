<?php

/**
 * Class Cms_Model_Application_Page_Block_Text
 */
class Cms_Model_Application_Page_Block_Text extends Cms_Model_Application_Page_Block_Abstract {

    /**
     * Cms_Model_Application_Page_Block_Text constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Text';
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        if($this->getContent() || $this->getImage()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {
        $image = $this->saveImage($data['image']);

        $this
            ->setContent($data['text'])
            ->setSize($data['size'])
            ->setAlignment($data['alignment'])
            ->setImage($image)
        ;

        return $this;
    }

}