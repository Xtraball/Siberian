<?php

class System_Controller_Backoffice_Default extends Backoffice_Controller_Default {

    static public $crmApiUrl = "http://krypton.siberiancms.com";

    public function findallAction() {

        $data = $this->_findconfig();
        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $this->_save($data);

                $data = array(
                    "success" => 1,
                    "message" => __("Info successfully saved")
                );
            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

        } else {
            $data = array(
                "error" => 1,
                "message" => "An error occurred while saving"
            );
        }

        $this->_sendHtml($data);

    }

    protected function _findconfig() {
        
        $config = new System_Model_Config();
        $values = $config->findAll(array("code IN (?)" => $this->_codes));

        $data = array();
        foreach($this->_codes as $code) {
            $data[$code] = array();
        }

        foreach($values as $value) {
            $data[$value->getCode()] = array(
                "code" => $value->getCode(),
                "label" => __($value->getLabel()),
                "value" => $value->getValue()
            );
        }

        # Custom SMTP
        $api_model = new Api_Model_Key();
        $keys = $api_model::findKeysFor("smtp_credentials");
        $data["smtp_credentials"] = $keys->getData();

        return $data;
    }

    protected function _save($data) {

        # Custom SMTP
        $this->_saveSmtp($data);

        foreach($data as $code => $values) {
            if(empty($code)) {
                continue;
            }
            if(!in_array($code, $this->_codes)) {
                continue;
            }
            if($code == "favicon") {
                continue;
            }
            $config = new System_Model_Config();
            $config->find($code, "code");
            $config->setValue($values["value"])->save();
        }

        return $this;
    }

    /**
     * Save SMTP configuration
     *
     * @param $data
     */
    public function _saveSmtp($data) {
        if(!isset($data["smtp_credentials"])) {
            return $this;
        }

        $_data = $data["smtp_credentials"];

        $api_provider = new Api_Model_Provider();
        $api_key = new Api_Model_Key();

        $provider = $api_provider->find("smtp_credentials", "code");
        if($provider->getId()) {
            $keys = $api_key->findAll(array("provider_id = ?" => $provider->getId()));
            foreach($keys as $key) {
                $code = $key->getKey();
                if(isset($_data[$code])) {
                    $key->setValue($_data[$code])->save();
                }
            }
        }

        return $this;
    }

    public function generateanalyticsAction() {

        try {

            Analytics_Model_Aggregate::getInstance()->run(time() - 60 * 60 * 24);
            Analytics_Model_Aggregate::getInstance()->run(time());
            Analytics_Model_Aggregate::getInstance()->run(time() + 60 * 60 * 24);

            $data = array(
                "success" => 1,
                "message" => __("Your analytics has been computed.")
            );
        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);

    }

    public function generateanalyticsforperiodAction() {

        try {

            $data = Zend_Json::decode($this->getRequest()->getRawBody());
            if(count($data) !== 2 ) {
                throw new Exception("No period sent.");
            }

            $from = new Zend_Date($data['from'], __("MM/dd/yyyy"));
            $to = new Zend_Date($data['to'], __("MM/dd/yyyy"));

            $fromTimestamp = $from->toValue();
            $toTimestamp = $to->toValue();

            if($fromTimestamp > $toTimestamp) {
                throw new Exception("Invalid period, end date is before start date.");
            }

            if($toTimestamp - $fromTimestamp > 60 * 60 * 24 * 31) {
                throw new Exception("Period to long, please select less than one month.");
            }

            $currentTimestamp = $fromTimestamp;
            while($currentTimestamp <= $toTimestamp) {
                Analytics_Model_Aggregate::getInstance()->run($currentTimestamp);
                $currentTimestamp += 60 * 60 * 24;
            }

            $data = array(
                "success" => 1,
                "message" => __("Your analytics has been computed.")
            );

        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);

    }

    public function checksiberiancmslicenseAction() {
        try {
            $data = array(
                'host' => $_SERVER['SERVER_NAME'],
                'licenseKey' => System_Model_Config::getValueFor('siberiancms_key')
            );
            $json = json_encode($data);
            $client = new Zend_Http_Client(self::$crmApiUrl."/siberian-licenses/use");
            $client->setMethod(Zend_Http_Client::POST);
            $client->setAdapter('Zend_Http_Client_Adapter_Curl');
            $client->setHeaders(array("Content-type" => 'application/json'));
            $response = $client->setRawData($json)->request();
            if($response->getRawBody() === "License has no more activation left") {
                throw new Exception(__("License has no more activation left"));
            }
            if($response->getStatus() !== 200) {
                throw new Exception(__("Invalid license key"));
            }
            $data = array(
                "message" => __("License is valid")
            );
        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }
        $this->_sendHtml($data);
    }

}
