<?php

/**
 * Class Api_Backoffice_Key_ListController
 */
class Api_Backoffice_Key_ListController extends Backoffice_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "save" => [
            "tags" => ["front_mobile_load"],
        ],
    ];

    public $exclude_providers = [
        "cpanel",
        "plesk",
        "vestacp",
        "directadmin",
        "smtp_credentials",
    ];

    public function loadAction()
    {

        $payload = [
            "title" => sprintf('%s > %s',
                __('Settings'),
                __('Api Keys')),
            "icon" => "fa-key",
        ];

        $this->_sendJson($payload);

    }

    public function findallAction()
    {

        $api_provider = new Api_Model_Provider();
        $api_providers = $api_provider->findAll(["code NOT IN (?)" => $this->exclude_providers]);
        $data = [];

        foreach ($api_providers as $k => $api_provider) {

            $provider_name = "";
            if ($api_provider->getIcon()) {
                $provider_name = '<i class="fa ' . $api_provider->getIcon() . '"></i> ';
            }
            $provider_name .= $api_provider->getName();
            $data["apis"][$k]["provider_name"] = $provider_name;

            if (empty($data["apis"][$k]["keys"])) {
                $data["apis"][$k]["keys"] = [];
            }

            foreach ($api_provider->getKeys() as $key) {

                $data["apis"][$k]["keys"][] = [
                    "id" => $key->getId(),
                    "provider" => $api_provider->getCode(),
                    "key" => $key->getKey(),
                    "value" => $key->getValue()
                ];

            }
        }

        $this->_sendHtml($data);
    }

    public function saveAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if (__getConfig('is_demo')) {
                    // Demo version
                    throw new Exception("This is a demo version, these changes can't be saved");
                }

                $keysData = [];

                foreach ($data as $api_provider) {
                    foreach ($api_provider["keys"] as $key) {
                        $keysData[$key["id"]] = $key;
                    }
                }

                $key = new Api_Model_Key();
                $keys = $key->findAll();

                foreach ($keys as $key) {
                    if (!empty($keysData[$key->getId()])) {
                        $key->addData($keysData[$key->getId()])->save();
                    }
                }

                $data = [
                    "success" => 1,
                    "message" => $this->_("Api Keys successfully saved")
                ];

            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $this->_("An error occurred while saving. Please try again later.<br/>" . $e->getMessage())
                ];

            }

            $this->_sendHtml($data);

        }

    }

}
