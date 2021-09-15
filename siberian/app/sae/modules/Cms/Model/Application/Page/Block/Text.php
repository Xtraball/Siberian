<?php

/**
 * Class Cms_Model_Application_Page_Block_Text
 */
class Cms_Model_Application_Page_Block_Text extends Cms_Model_Application_Page_Block_Abstract
{

    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page_Block_Text::class;

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->getContent() || $this->getImage();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = [])
    {
        $image = $this->saveImage($data['image']);

        $this
            ->setContent(\Siberian\Xss::sanitize($data['text']))
            ->setSize($data['size'])
            ->setImagePosition($data['image_position'])
            ->setAlignment($data['alignment'])
            ->setImage($image);

        return $this;
    }

}