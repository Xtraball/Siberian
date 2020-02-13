<?php

class Analytics_Mobile_StoreController extends Application_Controller_Mobile_Default
{
    /**
     * array
     */
    const SUCCESS = [
        'success' => true,
        'message' => 'Metrics successfully added.'
    ];

    /**
     * array
     */
    const ERROR = [
        'error' => true,
        'message' => 'Bad parameters.'
    ];

    public function installationAction()
    {
        $payload = self::ERROR;
        try {
            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data['appId'] = $this->getApplication()->getId();
                $data['timestampGMT'] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if ($result = $sqlite_request->addAppInstallationMetric($data)) {
                    $payload = self::SUCCESS;
                }
            }
        } catch (\Exception $e) {
            $payload['message'] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function openingAction()
    {
        $payload = self::ERROR;
        try {
            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data['appId'] = $this->getApplication()->getId();
                $data['startTimestampGMT'] = gmmktime();
                $data['endTimestampGMT'] = gmmktime();

                $sqliteRequest = Analytics_Model_Store::getInstance();
                if ($result = $sqliteRequest->addAppLoadedMetric($data)) {
                    $payload = self::SUCCESS;
                    $payload['id'] = $result[0];
                } else {
                    $payload = self::ERROR;
                }
            }
        } catch (Exception $e) {
            $payload['message'] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function closingAction()
    {
        $payload = self::ERROR;
        try {
            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $metric_data = ["endTimestampGMT = '" . gmmktime() . "'"];

                $sqlite_request = Analytics_Model_Store::getInstance();
                if ($result = $sqlite_request->addAppLoadedMetric($metric_data, $data['id'])) {
                    $payload = self::SUCCESS;
                }
            }
        } catch (\Exception $e) {
            $payload['message'] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function pageopeningAction()
    {
        $payload = self::ERROR;
        try {
            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data['timestampGMT'] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if ($result = $sqlite_request->addAppPageNavigationMetric($data)) {
                    $payload = self::SUCCESS;
                }
            }
        } catch (Exception $e) {
            $payload['message'] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function productopeningAction()
    {
        $payload = self::ERROR;
        try {
            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $data['timestampGMT'] = gmmktime();

                $sqlite_request = Analytics_Model_Store::getInstance();
                if ($result = $sqlite_request->addAppMcommerceProductNavigationMetric($data)) {
                    $payload = self::SUCCESS;
                }
            }

        } catch (Exception $e) {
            $payload['message'] = $e->getMessage();
        }

        $this->_sendJson($payload);
    }

    public function productsoldAction()
    {
        $payload = self::ERROR;

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            $metric_value = $data;
            unset($metric_value['products']);
            $metric_value['timestampGMT'] = gmmktime();

            foreach ($data['products'] as $product) {
                $metric_value['productId'] = $product['id'];
                $metric_value['categoryId'] = $product['category_id'];
                $metric_value['quantity'] = $product['quantity'];

                $sqlite_request = Analytics_Model_Store::getInstance();
                if ($result = $sqlite_request->addAppMcommerceProductSoldMetric($metric_value)) {
                    $payload = self::SUCCESS;
                }
            }
        }

        $this->_sendJson($payload);
    }


}