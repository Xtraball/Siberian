<?php

class Media_Mobile_Gallery_Video_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if ($gallery_id = $this->getRequest()->getParam("gallery_id") AND $offset = $this->getRequest()->getParam('offset', 1)) {

            try {

                $data = array("collection" => array());

                $video = new Media_Model_Gallery_Video();
                $video->find($gallery_id);

                if (!$video->getId() OR $video->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    throw new Exception($this->_('An error occurred while loading pictures. Please try later.'));
                }

                $videos = $video->setOffset($offset)->getVideos();
                $icon_url = $this->_getColorizedImage($this->getCurrentOptionValue()->getIconId(), $this->getApplication()->getBlock('background')->getColor());

                foreach ($videos as $key => $link) {

                    $key += $offset;
                    
                    $data["collection"][] = array(
                        "offset" => $key,
                        "video_id" => $link->getVideoId(),
                        "is_visible" => false,
                        "url" => $link->getLink(),
                        "url_embed" => $link->getLink(),
                        "cover_url" => $link->getImage(),
                        "title" => $link->getTitle(),
                        "description" => $link->getDescription(),
                        "icon_url" => $icon_url
                    );

                }


            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);
        }

    }

}