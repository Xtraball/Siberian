<?php

class Media_Model_Db_Table_Gallery_Video_Youtube extends Core_Model_Db_Table {

    protected $_name = "media_gallery_video_youtube";
    protected $_primary = "gallery_id";

    public function getFields() {
        $fields = array_keys($this->_db->describeTable($this->_name));
        return array_combine($fields, $fields);
    }

}
