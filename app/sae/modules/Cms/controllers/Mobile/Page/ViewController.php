<?php

class Cms_Mobile_Page_ViewController extends Application_Controller_Mobile_Default
{


    public function _toJson($block) {

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


    public function findAction() {
        if($value_id = $this->getRequest()->getParam('value_id')
           && $page_id = $this->getRequest()->getParam('page_id')) {

            try {

                $option_value = $this->getCurrentOptionValue();

                $page = new Cms_Model_Application_Page();
                $page->find($page_id);

                $blocks = $page->getBlocks();
                $json = array();

                foreach($blocks as $block) {
                    $json[] = $this->_toJson($block);
                }

                $data = array(
                    "blocks" => $json,
                    "page_title" => $page->getTitle() ? $page->getTitle() : $option_value->getTabbarName(),
                    "picture" => $page->getPictureUrl(),
                    "social_sharing_active" => $option_value->getSocialSharingIsActive()
                );

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendHtml($data);
    }

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $option = $this->getCurrentOptionValue();

                $page_id = $this->getRequest()->getParam('page_id');
                $page = new Cms_Model_Application_Page();

                if ($page_id) {
                    $page->find($page_id);
                } else if ($option->getCode() == "places" AND !$page_id) {
                    throw new Exception($this->_("An error occurred during process. Please try again later."));
                } else {
                    $page->find($option->getId(), 'value_id');
                }

                $blocks = $page->getBlocks();
                $data = array("blocks" => array());

                foreach ($blocks as $block) {
                    $data["blocks"][] = $this->_toJson($block);
                }

                if($option->getCode() == "places") {
                    $data["page"] = array(
                        "title" => $page->getTitle(),
                        "subtitle" => $page->getContent(),
                        "picture" => $page->getPictureUrl() ? $this->getRequest()->getBaseUrl().$page->getPictureUrl() : null,
                        "show_image" => $page->getMetadataValue('show_image'),
                        "show_titles" => $page->getMetadataValue('show_titles')
                    );
                }

                $data["page_title"] = $page->getTitle() ? $page->getTitle() : $option->getTabbarName();
                $data["social_sharing_active"] = $option->getSocialSharingIsActive();

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

    public function findblockAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND
            $block_id = $this->getRequest()->getParam('block_id')) {

            try {

                $page_id = $this->getRequest()->getParam("page_id");
                $option = $this->getCurrentOptionValue();

                $page_id = $this->getRequest()->getParam('page_id');
                $page = new Cms_Model_Application_Page();

                if ($page_id) {
                    $page->find($page_id);
                } else if ($option->getCode() == "places" AND !$page_id) {
                    throw new Exception($this->_("An error occurred during process. Please try again later."));
                } else {
                    $page->find($option->getId(), 'value_id');
                }

                $blocks = $page->getBlocks();
                $data = array("block" => array());

                foreach ($blocks as $block) {
                    if($block->getBlockId() == $block_id) {
                        $data["block"] = $this->_toJson($block);
                    }
                }

                if($page->getPictureUrl()) {
                    $data["block"]["picture_url"] = $this->getRequest()->getBaseUrl().$page->getPictureUrl();
                }

                $data["page_title"] = $page->getTitle() ? $page->getTitle() : $option->getTabbarName();

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

}