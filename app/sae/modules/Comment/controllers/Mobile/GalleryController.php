<?php

class Comment_Mobile_GalleryController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        // Functionality shared with NewsWall, but only available for FanWall
        if ($this->getCurrentOptionValue()->getCode() != "fanwall") return;

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $comment = new Comment_Model_Comment();
            $comments = $comment->findAllWithPhoto($value_id);

            $data = array(
                "collection" => array()
                );

            foreach($comments as $comment) {
                $data['collection'][] = array(
                    "id" => $comment->getId(),
                    "link" => $this->getPath("comment/mobile_view", array("value_id" => $value_id, "comment_id" => $comment->getId())),
                    "src" => $comment->getImageUrl() ? $this->getRequest()->getBaseUrl().$comment->getImageUrl() : null
                );
            }

            $this->_sendHtml($data);
        }
    }
}