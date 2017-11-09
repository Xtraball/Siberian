<?php

class Media_Mobile_Gallery_Image_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {
        try {
            if ($value_id = $this->getRequest()->getParam('value_id')) {
                try {
                    $images = (new Media_Model_Gallery_Image())
                        ->findAll([
                            'value_id' => $value_id
                        ]);

                    $galleries = [];
                    foreach($images as $image) {
                        $galleries[] = array(
                            'id' => $image->getGalleryId(),
                            'name' => $image->getLabel() ? $image->getLabel() : $image->getName(),
                            'type' => $image->getTypeId(),
                        );
                    }

                    $payload = [
                        'success' => true,
                        'galleries' => $galleries,
                        'page_title' => $this->getCurrentOptionValue()->getTabbarName(),
                        'header_right_button' => [
                            'picto_url' =>  $this->_getColorizedImage($this->_getImage('pictos/more.png', true),
                                $this->getApplication()->getBlock('subheader')->getColor())
                        ]
                    ];
                } catch (Exception $e) {
                    $payload = [
                        'error' => true,
                        'message' => $e->getMessage()
                    ];
                }
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => __('An unknown error occurred, please try again later.')
            ];
        }

        $this->_sendJson($payload);
    }

}
