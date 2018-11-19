<?php

/**
 * Class Cms_Model_Application_Page_Block_Abstract
 */
abstract class Cms_Model_Application_Page_Block_Abstract extends Core_Model_Default
{

    /**
     * @var null
     */
    public $option_value = null;

    /**
     * @var null
     */
    public $cms_page_block = null;

    /**
     * @return mixed
     */
    public abstract function isValid();

    /**
     * @param $option_value
     * @return $this
     */
    public function setOptionValue($option_value)
    {
        $this->option_value = $option_value;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = [])
    {
        $this->setData($data);

        return $this;
    }

    /**
     * @param $block_type
     * @param $page
     * @param $block_position
     * @return $this
     */
    public function createBlock($block_type, $page, $block_position)
    {
        $cms_application_block = new Cms_Model_Application_Block();
        $cms_application_block->find($block_type, "type");

        $cms_page_block = new Cms_Model_Application_Page_Block();
        $cms_page_block
            ->setBlockId($cms_application_block->getId())
            ->setPageId($page->getId())
            ->setPosition($block_position)
            ->save();

        $this->cms_page_block = $cms_page_block;

        # Set the value_id
        $this->setValueId($cms_page_block->getId());

        return $this;
    }

    /**
     * @return $this|bool
     */
    public function save_v2()
    {
        # Skip when invalid
        if (!$this->isValid()) {
            # Try to clean-up the mess
            if ($this->cms_page_block) {
                $this->cms_page_block->delete();
            }

            return false;
        }

        return parent::save();
    }

    /**
     * Helper to save/update images
     *
     * @param $option_value
     * @param $image
     * @return null|string
     */
    public function saveImage($image)
    {
        return Siberian_Feature::saveImageForOption($this->option_value, $image);
    }

    /**
     * @return null|string
     */
    public function getImageUrl()
    {
        return $this->getImage() ? Application_Model_Application::getImagePath() . $this->getImage() : null;
    }
}
