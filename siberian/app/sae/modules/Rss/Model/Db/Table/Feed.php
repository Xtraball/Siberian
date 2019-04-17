<?php

/**
 * Class Rss_Model_Db_Table_Feed
 */
class Rss_Model_Db_Table_Feed extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "rss_feed";
    /**
     * @var string
     */
    protected $_primary = "feed_id";

    /**
     * @param $positions
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function updatePositions($positions)
    {
        foreach ($positions as $pos => $feed_id) {
            $this->_db->update($this->_name, ['position' => $pos], ['feed_id = ?' => $feed_id]);
        }

        return $this;
    }

    /**
     * @param $valueId
     * @return string
     */
    public function getLastPosition($valueId)
    {
        $select = $this
            ->select("position")
            ->from($this->_name)
            ->where("rss_feed.value_id = ?", $valueId)
            ->order("rss_feed.position DESC");

        return $this->_db->fetchRow($select);
    }
}