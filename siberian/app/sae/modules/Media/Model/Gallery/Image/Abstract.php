<?php

/**
 * Class Media_Model_Gallery_Image_Abstract
 */
abstract class Media_Model_Gallery_Image_Abstract extends Core_Model_Default {

    const DISPLAYED_PER_PAGE = 10;

    protected $_images;

    abstract public function getImages($offset, $limit = self::DISPLAYED_PER_PAGE);

}
