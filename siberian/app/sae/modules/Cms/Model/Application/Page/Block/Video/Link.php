<?php

/**
 * Class Cms_Model_Application_Page_Block_Video_Link
 */
class Cms_Model_Application_Page_Block_Video_Link extends Cms_Model_Application_Page_Block_Abstract
{

    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page_Block_Video_Link::class;

    /**
     * @return bool|mixed
     */
    public function isValid()
    {
        if ($this->getLink()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = [])
    {
        $this
            ->setDescription($data["description"])
            ->setLink($data["video"])
            ->setImage($data["image"]);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->getImage() ? Application_Model_Application::PATH_IMAGE . $this->getImage() : null;
    }

}
