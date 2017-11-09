<?php

class Media_Model_Db_Table_Gallery_Music_Elements extends Core_Model_Db_Table {

    protected $_name = "media_gallery_music_elements";
    protected $_primary = "position_id";

    public function getLastElementsPosition() {

        $select = $this->select()
            ->from($this->_name, array('position'))
            ->order('position DESC')
            ->limit(1)
        ;

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

    public function updatePositions($element_id, $type, $pos) {
        $this->_db->update($this->_name, array('position' => $pos), array($type.'_id = ?' => $element_id));
    }

}
