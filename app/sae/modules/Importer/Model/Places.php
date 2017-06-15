<?php

class Importer_Model_Places extends Importer_Model_Importer_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);

    }

    public function importFromFacebook($data, $app_id = null) {
        try{
            if($data["location"]["latitude"] AND $data["location"]["longitude"]) {
                $place = new Cms_Model_Application_Page();
                $new_data = $this->_prepareDataFromFacebook($data, $app_id);
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data["value_id"]);
                $place->edit_v2($option_value,$new_data);
                return true;
            } else {
                return false;
            }
        } catch(Siberian_Exception $e) {
            return false;
        }
    }

    private function _prepareDataFromFacebook($data, $app_id) {
        //First we copy the cover if needed
        if($data["cover"] AND $data["cover"]["source"]) {
            try{
                $folder = Core_Model_Directory::getTmpDirectory(true).'/';
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $cover_name = uniqid().".jpg";
                $cover = $folder.$cover_name;
                file_put_contents($cover, file_get_contents($data["cover"]["source"]));
                $cover = $cover_name;
            } catch(Siberian_Exception $e) {
                $cover = null;
            }
        }

        $structured_data = array(
            "value_id" => $data["value_id"],
            "page_id" => "new",
            "cms_type" => "places",
            "title" => $data["name"],
            "content" => $data["description"] ? $data["description"] : $data["about"],
            "metadata" => array(
                "show_image" => 1,
                "show_titles" => 1
            )
        );

        if($data["location"]["latitude"] AND $data["location"]["longitude"]) {
            $structured_data["block"] = array("new" => array(
                "address" => array(
                    "label" => $data["name"],
                    "address" => $data["location"]["street"] ." ".$data["location"]["zip"]." ".$data["location"]["city"],
                    "latitude" => $data["location"]["latitude"],
                    "longitude" => $data["location"]["longitude"],
                    "show_address" => 1,
                    "show_geolocation_button" => 1
                )
            ));
        }

        if($cover) {
            $structured_data["places_file"] = $cover;
        }

        return $structured_data;
    }

}
