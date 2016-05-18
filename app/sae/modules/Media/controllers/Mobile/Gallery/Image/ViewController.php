<?php

class Media_Mobile_Gallery_Image_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if ($gallery_id = $this->getRequest()->getParam("gallery_id")) {

            try {

                $offset = $this->getRequest()->getParam('offset', 0);
                $data = array("collection" => array());

                $image = new Media_Model_Gallery_Image();
                $image->find($gallery_id);

                if (!$image->getId() OR $image->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    throw new Exception($this->_('An error occurred while loading pictures. Please try later.'));
                }

                $images = $image->setOffset($offset)->getImages();

                foreach ($images as $key => $link) {
                    $key+=$offset;
                    $data["collection"][] = array(
                        "offset" => $link->getOffset(),
                        "gallery_id" => $key,
                        "is_visible" => false,
                        "src" => stripos($link->getImage(), "http") === false ? $this->getRequest()->getBaseUrl().$link->getImage() : $link->getImage(),
                        "sub" => $link->getTitle() ? $link->getTitle() : $link->getDescription(),
                        "title" => $link->getTitle(),
                        "description" => $link->getDescription(),
                        "author" => $link->getAuthor()
                    );
                }

                if($image->getTypeId() != "custom") {
                    $data["show_load_more"] = count($data["images"]) > 0;
                } else {
                    $data["show_load_more"] = (($key - $offset) + 1) > (Media_Model_Gallery_Image_Abstract::DISPLAYED_PER_PAGE - 1) ? true : false;
                }

            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);
        }

    }

}
