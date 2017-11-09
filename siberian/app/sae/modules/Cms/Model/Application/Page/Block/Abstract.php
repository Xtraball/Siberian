<?php

abstract class Cms_Model_Application_Page_Block_Abstract extends Core_Model_Default {

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
    public function setOptionValue($option_value) {
        $this->option_value = $option_value ;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {
        $this->setData($data);

        return $this;
    }

    /**
     * @param $block_type
     * @param $page
     * @param $block_position
     * @return $this
     */
    public function createBlock($block_type, $page, $block_position) {
        $cms_application_block = new Cms_Model_Application_Block();
        $cms_application_block->find($block_type, "type");

        $cms_page_block = new Cms_Model_Application_Page_Block();
        $cms_page_block
            ->setBlockId($cms_application_block->getId())
            ->setPageId($page->getId())
            ->setPosition($block_position)
            ->save()
        ;

        $this->cms_page_block = $cms_page_block;

        # Set the value_id
        $this->setValueId($cms_page_block->getId());

        return $this;
    }

    /**
     * @return $this|bool
     */
    public function save_v2() {
        # Skip when invalid
        if(!$this->isValid()) {
            # Try to clean-up the mess
            if($this->cms_page_block) {
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
    public function saveImage($image) {
        return Siberian_Feature::saveImageForOption($this->option_value, $image);
    }

    public function getImageUrl() {
        return $this->getImage() ? Application_Model_Application::getImagePath().$this->getImage() : null;
    }

    public function getJSONData() {
        $block = new Cms_Model_Application_Block($_datas);
        $block->unsMobileTemplate()->unsTemplate();
        $block_data = $block->getData();

        switch($block->getType()) {
            case "text":
                $block_data["image_url"] = $block->getImageUrl() ? $this->getRequest()->getBaseUrl() . $block->getImageUrl() : null;
            break;
            case "address":
                $block_data["show_address"] = !!($block_data["show_address"]);
                $block_data["show_geolocation_button"] = !!($block_data["show_geolocation_button"]);
            break;
            case "image":
            case "slider":
                $library = new Cms_Model_Application_Page_Block_Image_Library();
                $libraries = $library->findAll(array('library_id' => $block->getLibraryId()), 'image_id ASC', null);
                $block_data["gallery"] = array();
                foreach($libraries as $image) {
                    $block_data["gallery"][] = array(
                        "id" => $image->getId(),
                        "src" => $this->getRequest()->getBaseUrl().$image->getImageFullSize()
                    );
                }
            break;
            case "video":
                $video = $block->getObject();
                $block_data["video_type_id"] = $video->getTypeId();
                $block_data["cover_url"] = $video->getImageUrl();
                $url_embed = $url = $video->getLink();
                $video_id = $video->getId();
                if($video->getTypeId() == "youtube") {
                    $url_embed = "https://www.youtube.com/embed/{$video->getYoutube()}?autoplay=1";
                    $url = "https://www.youtube.com/watch?v={$video->getYoutube()}&autoplay=1";
                    $video_id = $video->getYoutube();
                }

                if($video->getTypeId() == "link") {
                    $block_data["cover_url"] = $video->getImageUrl() ? $this->getRequest()->getBaseUrl().$video->getImageUrl() : null;
                }
                $block_data["url_embed"] = $url_embed;
                $block_data["url"] = $url;
                $block_data["video_id"] = $video_id;
                $block_data["title"] = $block_data["description"];
            break;
            case "file":
                $image_path = Application_Model_Application::getImagePath();
                $block_data["file_url"] = $image_path.$block->getName();
                $info = pathinfo($block_data["file_url"]);
                $block_data["file_url"] = $this->getRequest()->getBaseUrl().$block_data["file_url"];
                $filename = mb_strlen($info["filename"]) > 10 ? mb_substr($info["filename"],0,9)."...".$info["extension"]:$info["filename"].".".$info["extension"];
                $block_data["display_name"] = $filename;
                break;
            break;
        }

        return $block_data;

    }
}
