<?php

class Backoffice_Advanced_ConfigurationController extends System_Controller_Backoffice_Default {

    public $_codes  = array(
        "disable_cron",
        "environment",
        "update_channel",
    );

    public function loadAction() {

        $html = array(
            "title" => __("Advanced")." > ".__("Configuration"),
            "icon" => "fa-toggle-on",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $data = $this->_findconfig();

        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                $this->_save($data);

                $message = __("Configuration successfully saved");

                if(isset($data["environment"]) && in_array($data["environment"]["value"], array("production", "development"))) {
                    $config_file = Core_Model_Directory::getBasePathTo("config.php");
                    if(is_writable($config_file)) {
                        $contents = file_get_contents($config_file);
                        $contents = preg_replace('/"(development|production)"/im', '"'.$data["environment"]["value"].'"', $contents);
                        file_put_contents($config_file, $contents);
                    } else {
                        $message = __("Configuration partially saved")."<br />".__("Error: unable to write Environment in config.php");
                    }
                }

                $data = array(
                    "success" => 1,
                    "message" => $message,
                );
            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage(),
                );
            }

            $this->_sendHtml($data);

        }

    }

}
