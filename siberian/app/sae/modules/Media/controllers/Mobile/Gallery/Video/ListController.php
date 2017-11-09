<?php

class Media_Mobile_Gallery_Video_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam("value_id")) {

            try {

                $video = new Media_Model_Gallery_Video();
                $videos = $video->findAll(array('value_id' => $value_id));
                $data = array("collection" => array());
                $has_youtube_videos = false;

                foreach($videos as $video) {
                    $data["collection"][] = array(
                        "id" => $video->getId(),
                        "name" => $video->getName(),
                        "type" => $video->getTypeId(),
                        "search_by" => $video->getType(),
                        "search_keyword" => $video->getParam()
                    );
                    if($video->getTypeId() == "youtube") {
                        $has_youtube_videos = true;
                    }
                }

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
                $data["displayed_per_page"] = Media_Model_Gallery_Video_Abstract::DISPLAYED_PER_PAGE;
                $data["header_right_button"]["picto_url"] = $this->_getColorizedImage($this->_getImage('pictos/more.png', true), $this->getApplication()->getBlock('subheader')->getColor());
                if($has_youtube_videos) {
                    $data["youtube_key"] = Api_Model_Key::findKeysFor('youtube')->getApiKey();
                }

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}