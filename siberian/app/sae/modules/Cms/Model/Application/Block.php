<?php

/**
 * Class Cms_Model_Application_Block
 */
class Cms_Model_Application_Block extends Core_Model_Default
{

    /**
     * @var
     */
    protected $_object;

    /**
     * Cms_Model_Application_Block constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Block';
        return $this;
    }

    /**
     * @param $page_id
     * @return mixed
     */
    public function findByPage($page_id)
    {
        return $this->getTable()->findByPage($page_id);
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        if (!$this->_object AND $this->getType()) {
            $type = $this->getType() ? ucfirst($this->getType()) : 'Text';
            $class = 'Cms_Model_Application_Page_Block_' . $type;
            $this->_object = new $class();
            $this->_object->find($this->getValueId(), 'value_id');
        }

        return $this->_object;
    }

    /**
     * @return bool|Push_Model_Message[]
     */
    public function getLibrary()
    {
        if ($this->getLibraryId()) {
            $model_library = new Cms_Model_Application_Page_Block_Image_Library();
            $result = $model_library->findAll(["library_id" => $this->getLibraryId()]);

            return $result;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->getObject() ? $this->getObject()->getImageUrl() : '';
    }

    /**
     * @param $base_url
     * @return array|mixed|null|string
     */
    public function _toJson($base_url)
    {
        $block_data = $this->getData();

        switch ($this->getType()) {
            case "address":
                $block_data["latitude"] = (float) $block_data["latitude"];
                $block_data["longitude"] = (float) $block_data["longitude"];
                $block_data["show_phone"] = !!($block_data["show_phone"]);
                $block_data["show_website"] = !!($block_data["show_website"]);
                $block_data["show_address"] = !!($block_data["show_address"]);
                $block_data["show_geolocation_button"] = !!($block_data["show_geolocation_button"]);

                break;
            case "button":
                $image = Core_Model_Directory::getBasePathTo("/images/application/" . $this->getIcon());
                if (is_readable($image) && is_file($image)) {
                    $block_data["icon"] = $base_url . "/images/application/" . $this->getIcon();
                } else {
                    # Force empty images to be sure old/new cms flavors
                    $block_data["icon"] = "";
                }

                break;
            case "text":
                $image = Core_Model_Directory::getBasePathTo($this->getImageUrl());
                if (is_readable($image) && is_file($image)) {
                    $block_data["image_url"] = $base_url . $this->getImageUrl();
                } else {
                    # Force empty images to be sure old/new cms flavors
                    $block_data["image_url"] = "";
                    $block_data["image"] = "";
                }

                break;
            case "image":
            case "slider":
            case "cover":
                $library = new Cms_Model_Application_Page_Block_Image_Library();
                $libraries = $library->findAll(['library_id' => $this->getLibraryId()], 'image_id ASC', null);
                $block_data["gallery"] = [];
                foreach ($libraries as $image) {

                    # Should be remove at some point (+6months from Feb/2016)
                    # Special case to handle badly saved COVER CMS Blocks
                    $path_image = $image->getImageFullSize();
                    if ($this->getType() == "cover" && !is_readable(Core_Model_Directory::getBasePathTo($path_image))) {
                        $path_image = $image->getImage();

                        # Try to fix the incorrect COVER
                        if (strpos($image->getData("image_fullsize_url"), "/") === false) {
                            $image->setData("image_fullsize_url", $image->getData("image_url"))->save();
                        }
                    }

                    $block_data["gallery"][] = [
                        "id" => $image->getId(),
                        "src" => $base_url . $path_image
                    ];

                }
                break;
            case "video":
                $video = $this->getObject();
                $video_instance = $video->getTypeInstance();
                $block_data["cover_url"] = $video->getImageUrl();
                $url_embed = $url = $video->getLink();
                $video_id = $video->getId();
                if ($video->getTypeId() == "youtube") {
                    $url_embed = "https://www.youtube.com/embed/{$video_instance->getYoutube()}?autoplay=1";
                    $url = "https://www.youtube.com/watch?v={$video_instance->getYoutube()}&autoplay=1";
                    $video_id = $video_instance->getYoutube();
                }
                if ($video->getTypeId() == "link") {
                    $url_embed = $video_instance->getLink();
                    $url = $video_instance->getLink();
                    $block_data["cover_url"] = $video->getImageUrl() ? $base_url . $video->getImageUrl() : null;
                }
                if ($video->getTypeId() == "podcast") {
                    $podcast = $video_instance->getList($video_instance->getSearch(), $video_instance->getLink());
                    $url_embed = $podcast->getLink();
                    $url = $podcast->getLink();
                    $block_data["cover_url"] = $podcast->getImage();
                }
                $block_data["url_embed"] = $url_embed;
                $block_data["url"] = $url;
                $block_data["video_id"] = $video_id;
                $block_data["title"] = $block_data["description"];
                break;
            case "file":
                $image_path = Application_Model_Application::getImagePath();
                $block_data["file_url"] = $image_path . $this->getName();
                $info = pathinfo($block_data["file_url"]);
                $block_data["file_url"] = $base_url . $block_data["file_url"];
                $filename = mb_strlen($info["filename"]) > 15 ? mb_substr($info["filename"], 0, 14) . "..." . $info["extension"] : $info["filename"] . "." . $info["extension"];
                $block_data["display_name"] = $filename;
                if (!empty($block_data["label"])) {
                    $block_data["display_name"] = $block_data["label"];
                }
                break;
        }

        return $block_data;

    }
}
