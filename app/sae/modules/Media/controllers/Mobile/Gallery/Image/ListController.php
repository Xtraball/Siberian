<?php

class Media_Mobile_Gallery_Image_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if ($value_id = $this->getRequest()->getParam("value_id")) {

            try {

                $image = new Media_Model_Gallery_Image();
                $images = $image->findAll(array('value_id' => $value_id));
                $data = array("galleries" => array());

                foreach($images as $image) {
                    $data["galleries"][] = array(
                        "id" => $image->getId(),
                        "name" => $image->getLabel() ? $image->getLabel() : $image->getName(),
                        "type" => $image->getTypeId(),
                    );
                }

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
                $data["header_right_button"]["picto_url"] = $this->_getColorizedImage($this->_getImage('pictos/more.png', true), $this->getApplication()->getBlock('subheader')->getColor());

            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);
        }
    }

}
