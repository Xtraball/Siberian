<?php

/**
 * Class Weblink_Model_Db_Table_Weblink_Link
 */
class Weblink_Model_Db_Table_Weblink_Link extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = 'weblink_link';
    /**
     * @var string
     */
    protected $_primary = 'link_id';

    /**
     * @param $webLinkId
     * @return int
     */
    public function getMaxPosition($webLinkId): int
    {
        $select = $this->_db->select()
            ->from($this->_name, ['max' => 'MAX(position)'])
            ->where('weblink_id = ?', $webLinkId);

        $result = $this->_db->fetchOne($select);

        return is_numeric($result) ? $result : 0;
    }
}