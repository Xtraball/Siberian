<?php

/**
 * Class Media_Model_Db_Table_Gallery_Image
 */
class Media_Model_Db_Table_Gallery_Image extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = 'media_gallery_image';
    /**
     * @var string
     */
    protected $_primary = 'gallery_id';

    /**
     * @param $galleryId
     * @param $position
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function updatePosition($galleryId, $position)
    {
        $this->getAdapter()->query("UPDATE `media_gallery_image` SET `position` = ? WHERE `gallery_id` = ?", [$position, $galleryId]);
    }
}
