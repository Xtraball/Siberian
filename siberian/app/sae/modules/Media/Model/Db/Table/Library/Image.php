<?php

/**
 * Class Media_Model_Db_Table_Library_Image
 */
class Media_Model_Db_Table_Library_Image extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = "media_library_image";
    /**
     * @var string
     */
    protected $_primary = "image_id";

    /**
     * @param $values
     * @param null $order
     * @param array $params
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAll($values, $order = null, $params = [])
    {
        if (is_null($order)) $order = 'position ASC';

        return parent::findAll($values, $order, $params);
    }

    /**
     * @param $positions
     * @throws Zend_Db_Adapter_Exception
     */
    public function updatePositions($positions)
    {
        foreach ($positions as $position => $image_id) {
            $this->_db->update($this->_name, array('position' => $position), array('image_id = ?' => $image_id));
        }
    }
}
