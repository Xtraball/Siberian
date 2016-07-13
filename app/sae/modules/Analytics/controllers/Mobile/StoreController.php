<?php

class Analytics_Mobile_StoreController extends Application_Controller_Mobile_Default {

    public function installationAction() {
        $html = array('error' => '1', 'message' => 'Bad parameters.');

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $data["appId"] = $this->getApplication()->getId();
            $data["timestampGMT"] = gmmktime();

            $sqlite_request = Analytics_Model_Store::getInstance();
            if($result = $sqlite_request->addAppInstallationMetric($data)) {
                $html = array('success' => '1', 'message' => 'Metrics successfully added.');
            }
        }

        $this->_sendHtml($html);
    }

    public function openingAction() {
        $html = array('error' => '1', 'message' => 'Bad parameters.');

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $data["appId"] = $this->getApplication()->getId();
            $data["startTimestampGMT"] = gmmktime();
            $data["endTimestampGMT"] = gmmktime();

            $sqlite_request = Analytics_Model_Store::getInstance();
            if($result = $sqlite_request->addAppLoadedMetric($data)) {
                $html = array('success' => '1', 'message' => 'Metrics successfully added.', 'id' => $result[0]);
            }
        }

        $this->_sendHtml($html);
    }

    public function closingAction() {
        $html = array('error' => '1', 'message' => 'Bad parameters.');

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $metric_data = array("endTimestampGMT = '".gmmktime()."'");

            $sqlite_request = Analytics_Model_Store::getInstance();
            if($result = $sqlite_request->addAppLoadedMetric($metric_data, $data['id'])) {
                $html = array('success' => '1', 'message' => 'Metrics successfully added.');
            }
        }

        $this->_sendHtml($html);
    }

    public function pageopeningAction() {
        $html = array('error' => '1', 'message' => 'Bad parameters.');

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $data["timestampGMT"] = gmmktime();

            $sqlite_request = Analytics_Model_Store::getInstance();
            if($result = $sqlite_request->addAppPageNavigationMetric($data)) {
                $html = array('success' => '1', 'message' => 'Metrics successfully added.');
            }
        }

        $this->_sendHtml($html);
    }

    public function productopeningAction() {
        $html = array('error' => '1', 'message' => 'Bad parameters.');

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $data["timestampGMT"] = gmmktime();

            $sqlite_request = Analytics_Model_Store::getInstance();
            if($result = $sqlite_request->addAppMcommerceProductNavigationMetric($data)) {
                $html = array('success' => '1', 'message' => 'Metrics successfully added.');
            }
        }

        $this->_sendHtml($html);
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

        $this->_sendHtml($html);
    }



}