<?php

class Cms_Model_Db_Table_Application_Page_Block_Image_Library extends Core_Model_Db_Table
{
    protected $_name = "cms_application_page_block_image_library";
    protected $_primary = "image_id";

    public function findLastLibrary() {
        $select = $this->select()
            ->from($this->_name, array('library_id'))
            ->order('library_id DESC')
            ->limit(1)
        ;

        $library_id = $this->_db->fetchOne($select);

        return !empty($library_id) ? $library_id : 0;

    }
    
}