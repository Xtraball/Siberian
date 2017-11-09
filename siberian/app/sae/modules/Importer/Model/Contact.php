<?php

class Importer_Model_Contact extends Importer_Model_Importer_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);

    }

    public function importFromFacebook($data, $app_id = null) {
        try{
            $contact = new Contact_Model_Contact();
            $new_data = $this->_prepareDataFromFacebook($data, $app_id);
            $contact->addData($new_data)->save();
            return true;
        } catch(Siberian_Exception $e) {
            return false;
        }
    }

    private function _prepareDataFromFacebook($data, $app_id) {
        //First we copy the cover if needed
        if($data["cover"] AND $data["cover"]["source"]) {
            try{
                $folder = Application_Model_Application::getBaseImagePath()."/".$app_id."/features/contact/".$data["value_id"]."/";
                $folder_cover = "/".$app_id."/features/contact/".$data["value_id"]."/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $cover_name = uniqid().".jpg";
                $cover = $folder.$cover_name;
                file_put_contents($cover, file_get_contents($data["cover"]["source"]));
                $cover = $folder_cover.$cover_name;
            } catch(Siberian_Exception $e) {
                $cover = null;
            }
        }

        $structured_data = array(
            "value_id" => $data["value_id"],
            "name" => $data["name"],
            "description" => $data["description"] ? $data["description"] : $data["about"],
            "facebook" => $data["link"],
            "website" => $data["website"],
            "email" => $data["emails"] ? $data["emails"][0] : "",
            "civility" => "",
            "firstname" => "",
            "lastname" => "",
            "street" => $data["location"]["street"] ? $data["location"]["street"] : "",
            "postcode" => $data["location"]["zip"] ? $data["location"]["zip"] : "",
            "city" => $data["location"]["city"] ? $data["location"]["city"] : "",
            "country" => $data["location"]["country"],
            "latitude" => $data["location"]["latitude"],
            "longitude" => $data["location"]["longitude"],
            "phone" => $data["phone"]
        );

        if($cover) {
            $structured_data["cover"] = $cover;
        }

        return $structured_data;
    }

}
