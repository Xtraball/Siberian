<?php

class Rss_Model_Db_Table_Feed extends Core_Model_Db_Table
{
    protected $_name = "rss_feed";
    protected $_primary = "feed_id";

    public function updatePositions($positions) {
        foreach($positions as $pos => $feed_id) {
            $this->_db->update($this->_name, array('position' => $pos), array('feed_id = ?' => $feed_id));
        }

        return $this;
    }
}