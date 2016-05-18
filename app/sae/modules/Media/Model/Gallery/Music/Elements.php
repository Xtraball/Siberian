<?php

class Media_Model_Gallery_Music_Elements extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Music_Elements';
        return $this;
    }

    public function updatePositions($element_id, $type, $pos) {
        $success = $this->getTable()->updatePositions($element_id, $type, $pos);
        return $success;
    }

    public function getNextElementsPosition() {
        $lastPosition = $this->getTable()->getLastElementsPosition();
        if(!$lastPosition) $lastPosition = 0;
        return ++$lastPosition;
    }

}
