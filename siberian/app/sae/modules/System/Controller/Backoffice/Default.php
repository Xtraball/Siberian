<?php

use Siberian\Json;
use Siberian\Provider;
use Siberian\Request;
use Siberian\Version;

/**
 * Class System_Controller_Backoffice_Default
 */
class System_Controller_Backoffice_Default extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function findallAction()
    {
        $this->_sendJson($this->_findconfig());
    }

    /**
     * @throws Zend_Exception
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $params = $request->getBodyParams();
        if (!empty($params)) {
            try {
                $this->_save($params);
                $payload = [
                    "success" => true,
                    "message" => __("Info successfully saved")
                ];
            } catch (\Exception $e) {
                $payload = [
                    "error" => true,
                    "message" => $e->getMessage()
                ];
            }

        } else {
            $payload = [
                "error" => true,
                "message" => __("An error occurred while saving")
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @return array
     * @throws Zend_Exception
     */
    protected function _findconfig()
    {
        $values = (new System_Model_Config())
            ->findAll(["code IN (?)" => $this->_codes]);

        $data = [];
        foreach ($this->_codes as $code) {
            $data[$code] = [];
        }

        foreach ($values as $value) {
            $data[$value->getCode()] = [
                "code" => $value->getCode(),
                "label" => __($value->getLabel()),
                "value" => $value->getValue()
            ];
        }

        # Custom SMTP
        $api_model = new Api_Model_Key();
        $keys = $api_model::findKeysFor("smtp_credentials");
        $data["smtp_credentials"] = $keys->getData();

        return $data;
    }

    /**
     * @param $data
     * @return $this
     * @throws Exception
     * @throws Siberian_Exception
     * @throws Zend_Exception
     */
    protected function _save($data)
    {
        if (__getConfig('is_demo')) {
            // Demo version
            throw new \Siberian\Exception("This is a demo version, these changes can't be saved");
        }

        # Required fields
        if (array_key_exists("main_domain", $data)) {
            // Raise error if empty!
            if (empty($data['main_domain']['value'])) {
                throw new \Siberian\Exception('#797-00: ' . __('Main domain is required!'));
            }

            // If input matches https?:// extract host part before saving!
            if (preg_match('/^https?:\/\//', $data['main_domain']['value'])) {
                $data['main_domain']['value'] = parse_url($data['main_domain']['value'], PHP_URL_HOST);
            }
        }

        # Custom SMTP
        $this->_saveSmtp($data);

        foreach ($data as $code => $values) {
            if (empty($code)) {
                continue;
            }

            if (!in_array($code, $this->_codes)) {
                continue;
            }

            if (!Version::is("SAE")) {
                if ($code === 'app_default_identifier_android') {
                    $regexAndroid = "/^([a-z]{1}[a-z_]*){2,10}\.([a-z]{1}[a-z0-9_]*){1,30}((\.([a-z]{1}[a-z0-9_]*){1,61})*)?$/i";

                    if (preg_match($regexAndroid, $values['value']) !== 1) {
                        throw new \Siberian\Exception(__("Your package name is invalid, format should looks like com.mydomain.androidid"));
                    }
                }

                if ($code === 'app_default_identifier_ios') {
                    $regexIos = "/^([a-z]){2,10}\.([a-z-]{1}[a-z0-9-]*){1,30}((\.([a-z-]{1}[a-z0-9-]*){1,61})*)?$/i";

                    if (preg_match($regexIos, $values['value']) !== 1) {
                        throw new \Siberian\Exception(__("Your bundle id is invalid, format should looks like com.mydomain.iosid"));
                    }
                }
            }

            if ($code === 'favicon') {
                continue;
            }

            __set($code, $values['value']);
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function _saveSmtp($data)
    {
        if (!isset($data["smtp_credentials"])) {
            return $this;
        }

        $_data = $data["smtp_credentials"];

        $api_provider = new Api_Model_Provider();
        $api_key = new Api_Model_Key();

        $provider = $api_provider->find("smtp_credentials", "code");
        if ($provider->getId()) {
            $keys = $api_key->findAll(["provider_id = ?" => $provider->getId()]);
            foreach ($keys as $key) {
                $code = $key->getKey();
                if (isset($_data[$code])) {
                    $key->setValue($_data[$code])->save();
                }
            }
        }

        return $this;
    }

    /**
     *
     */
    public function generateanalyticsAction()
    {
        try {
            Analytics_Model_Aggregate::getInstance()->run(time() - 60 * 60 * 24);
            Analytics_Model_Aggregate::getInstance()->run(time());
            Analytics_Model_Aggregate::getInstance()->run(time() + 60 * 60 * 24);

            $payload = [
                "success" => 1,
                "message" => __("Your analytics has been computed.")
            ];
        } catch (Exception $e) {
            $payload = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function generateanalyticsforperiodAction()
    {
        try {
            $data = Zend_Json::decode($this->getRequest()->getRawBody());
            if (count($data) !== 2) {
                throw new Exception("No period sent.");
            }

            $from = new Zend_Date($data['from'], 'MM/dd/yyyy');
            $to = new Zend_Date($data['to'], 'MM/dd/yyyy');

            $fromTimestamp = $from->toValue();
            $toTimestamp = $to->toValue();

            if ($fromTimestamp > $toTimestamp) {
                throw new Exception("Invalid period, end date is before start date.");
            }

            if ($toTimestamp - $fromTimestamp > 60 * 60 * 24 * 31) {
                throw new Exception("Period to long, please select less than one month.");
            }

            $currentTimestamp = $fromTimestamp;
            while ($currentTimestamp <= $toTimestamp) {
                Analytics_Model_Aggregate::getInstance()->run($currentTimestamp);
                $currentTimestamp += 60 * 60 * 24;
            }

            $data = [
                "success" => 1,
                "message" => __("Your analytics has been computed.")
            ];

        } catch (Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendHtml($data);

    }

    /**
     *
     */
    public function checksiberiancmslicenseAction()
    {
        try {
            $data = [
                'host' => $_SERVER['SERVER_NAME'],
                'licenseKey' => __get('siberiancms_key')
            ];

            $json = json_encode($data);

            $client = new Zend_Http_Client(Siberian\Provider::getLicenses()['use']['url'], [
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => [CURLOPT_SSL_VERIFYPEER => false],
            ]);
            $client->setMethod(Zend_Http_Client::POST);
            $client->setHeaders(["Content-type" => 'application/json']);
            $response = $client->setRawData($json)->request();
            if ($response->getRawBody() === "License has no more activation left") {
                throw new Exception(__("License has no more activation left"));
            }
            if ($response->getStatus() !== 200) {
                throw new Exception(__("Invalid license key"));
            }
            $data = [
                "message" => __("License is valid")
            ];
        } catch (Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
        }
        $this->_sendHtml($data);
    }

    /**
     * Alias action endpoint
     */
    public function getlicensetypeAction()
    {
        $this->_sendJson(self::getLicenseType());
    }

    /**
     * @return array
     */
    public static function getLicenseType(): array
    {
        try {
            $license = __get('siberiancms_key');
            $checkUrl = Provider::getLicenses()['check']['url'];

            $response = Request::post(
                $checkUrl,
                Json::encode([
                    'licenseKey' => $license
                ]),
                null,
                null,
                [
                    'content-type: application/json'
                ],
                [
                    'json_body' => true,
                    'timeout' => 30
                ]
            );

            $responseDecoded = Json::decode($response);
            if (Request::$statusCode !== 200) {
                throw new \Siberian\Exception('#080-00: ' . $responseDecoded['message']);
            }

            $payload = [
                'success' => true,
                'result' => $responseDecoded
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return $payload;
    }

}
