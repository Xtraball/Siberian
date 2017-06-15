<?php

class Importer_Model_Event extends Importer_Model_Importer_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);

    }

    public function importFromFacebook($data, $app_id = null) {
        try{
            $event = new Event_Model_Event();
            $event->addData($data)->save();
            return true;
        } catch(Siberian_Exception $e) {
            return false;
        }
    }

}
