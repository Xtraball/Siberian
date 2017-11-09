<?php

class Media_Model_Db_Table_Library_Image extends Core_Model_Db_Table {

    protected $_name = "media_library_image";
    protected $_primary = "image_id";

    public function findAll($values, $order, $params) {
        if(is_null($order)) $order = 'position ASC';

        return parent::findAll($values, $order, $params);
    }

    public function updatePositions($positions) {
        foreach($positions as $position => $image_id) {
            $this->_db->update($this->_name, array('position' => $position), array('image_id = ?' => $image_id));
        }
    }
}
