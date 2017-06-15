<?php

class Importer_Model_SocialFacebook extends Importer_Model_Importer_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);

    }

    public function importFromFacebook($data, $app_id = null) {
        try{
            $fb = new Social_Model_Facebook();
            $fb->addData($data)->save();
            return true;
        } catch(Siberian_Exception $e) {
            return false;
        }
    }

}
