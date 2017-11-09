<?php

class Media_Model_Db_Table_Gallery_Music_Track extends Core_Model_Db_Table {

    protected $_name = "media_gallery_music_track";
    protected $_primary = "track_id";

    public function getLastTrackPosition() {

        $select = $this->select()->from($this->_name, array('position'))
            ->order('position DESC')
            ->limit(1)
        ;

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

}
