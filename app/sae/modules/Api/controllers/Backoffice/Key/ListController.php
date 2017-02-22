<?php

class Api_Backoffice_Key_ListController extends Backoffice_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("front_mobile_load"),
        ),
    );

    public $exclude_providers = array(
        "cpanel",
        "plesk",
        "vestacp",
        "directadmin",
        "smtp_credentials",
    );

    public function loadAction() {

        $html = array(
            "title" => $this->_("Api Keys"),
            "icon" => "fa-key",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $api_provider = new Api_Model_Provider();
        $api_providers = $api_provider->findAll(array("code NOT IN (?)" => $this->exclude_providers));
        $data = array();

        foreach($api_providers as $k => $api_provider) {

            $provider_name = "";
            if($api_provider->getIcon()) {
                $provider_name = '<i class="fa '.$api_provider->getIcon().'"></i> ';
            }
            $provider_name .= $api_provider->getName();
            $data["apis"][$k]["provider_name"] = $provider_name;

            if(empty($data["apis"][$k]["keys"])) {
                $data["apis"][$k]["keys"] = array();
            }

            foreach($api_provider->getKeys() as $key) {

                $data["apis"][$k]["keys"][] = array(
                    "id" => $key->getId(),
                    "provider" => $api_provider->getCode(),
                    "key" => $key->getKey(),
                    "value" => $key->getValue()
                );

            }
        }

        $this->_sendHtml($data);
    }

    public function saveAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $keysData = array();

                foreach($data as $api_provider) {
                    foreach($api_provider["keys"] as $key) {
                        $keysData[$key["id"]] = $key;
                    }
                }

                $key = new Api_Model_Key();
                $keys = $key->findAll();

                foreach($keys as $key) {
                    if(!empty($keysData[$key->getId()])) {
                        $key->addData($keysData[$key->getId()])->save();
                    }
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Api Keys successfully saved")
                );

            } catch (Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $this->_("An error occurred while saving. Please try again later.<br/>".$e->getMessage())
                );

            }

            $this->_sendHtml($data);

        }

    }

}
