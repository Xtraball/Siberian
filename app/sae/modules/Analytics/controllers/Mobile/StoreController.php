<?php

class Analytics_Mobile_StoreController extends Application_Controller_Mobile_Default {

    public function installationAction() {
        $payload = array(
            "error" => true,
            "message" => "Bad parameters."
        );

        try {
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data["appId"] = $this->getApplication()->getId();
                $data["timestampGMT"] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if($result = $sqlite_request->addAppInstallationMetric($data)) {
                    $payload = array('success' => '1', 'message' => 'Metrics successfully added.');
                }
            }
        } catch (Exception $e) {
            $payload["message"] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function openingAction() {
        $payload = array(
            "error" => true,
            "message" => "Bad parameters."
        );

        try {
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data["appId"] = $this->getApplication()->getId();
                $data["startTimestampGMT"] = gmmktime();
                $data["endTimestampGMT"] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if($result = $sqlite_request->addAppLoadedMetric($data)) {
                    $payload = array('success' => '1', 'message' => 'Metrics successfully added.', 'id' => $result[0]);
                }
            }
        } catch (Exception $e) {
            $payload["message"] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function closingAction() {
        $payload = array(
            "error" => true,
            "message" => "Bad parameters."
        );

        try {
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $metric_data = array("endTimestampGMT = '".gmmktime()."'");

                $sqlite_request = Analytics_Model_Store::getInstance();
                if($result = $sqlite_request->addAppLoadedMetric($metric_data, $data['id'])) {
                    $payload = array('success' => '1', 'message' => 'Metrics successfully added.');
                }
            }
        } catch (Exception $e) {
            $payload["message"] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function pageopeningAction() {
        $payload = array(
            "error" => true,
            "message" => "Bad parameters."
        );

        try {
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data["timestampGMT"] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if($result = $sqlite_request->addAppPageNavigationMetric($data)) {
                    $payload = array('success' => '1', 'message' => 'Metrics successfully added.');
                }
            }
        } catch (Exception $e) {
            $payload["message"] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function productopeningAction() {
        $payload = array(
            "error" => true,
            "message" => "Bad parameters."
        );

        try {
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data["timestampGMT"] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if($result = $sqlite_request->addAppMcommerceProductNavigationMetric($data)) {
                    $payload = array('success' => '1', 'message' => 'Metrics successfully added.');
                }
            }

        } catch (Exception $e) {
            $payload["message"] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function productsoldAction() {
        $html = array('error' => '1', 'message' => 'Bad parameters.');

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $metric_value = $data;
            unset($metric_value['products']);
            $metric_value["timestampGMT"] = gmmktime();

            foreach($data['products'] as $product) {
                $metric_value['productId'] = $product['id'];
                $metric_value['categoryId'] = $product['category_id'];
                $metric_value['quantity'] = $product['quantity'];

                $sqlite_request = Analytics_Model_Store::getInstance();
                if($result = $sqlite_request->addAppMcommerceProductSoldMetric($metric_value)) {
                    $html = array('success' => '1', 'message' => 'Metrics successfully added.');
                }
            }
        }

        $this->_sendJson($html);
    }



}