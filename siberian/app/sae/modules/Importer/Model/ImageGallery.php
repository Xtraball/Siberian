<?php

class Importer_Model_ImageGallery extends Importer_Model_Importer_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);

    }

    public function importFromFacebook($data, $app_id = null) {
        try{
            $value_id = $data["value_id"];
            $page_id = $data["page_id"];
            foreach($data["albums"] as $tmp) {
                if($tmp["name"]) {
                    $gal = new Media_Model_Gallery_Image();
                    $gal->addData(array(
                        "value_id" => $value_id,
                        "type_id" => "facebook",
                        "name" => $page_id,
                        "label" => $tmp["name"]
                    ))->save();

                    $fb = new Media_Model_Gallery_Image_Facebook();
                    $fb->addData(array(
                        "name" => $tmp["name"],
                        "album_id" => $tmp["id"],
                        "gallery_id" => $gal->getId()))
                        ->save();
                }
            }

            return true;
        } catch(Siberian_Exception $e) {
            return false;
        }
    }
}
