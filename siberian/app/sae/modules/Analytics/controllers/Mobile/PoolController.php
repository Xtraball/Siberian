<?php

/**
 * Class Analytics_Mobile_PoolController
 */
class Analytics_Mobile_PoolController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function submitAction ()
    {
        try {
            $request = $this->getRequest();
            $pool = $request->getBodyParams();

            foreach ($pool as $singleRequest) {
                $url = $singleRequest['url'];
                try {
                    $this->$url($singleRequest['params']);
                } catch (\Exception $e) {
                    // Silently fails bad data request!
                }
            }
            
            $payload = [
                'success' => true,
                'message' => __('Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

    /**
     * @param $data
     * @return bool
     */
    public function _installation ($data)
    {
        $data["appId"] = $this->getApplication()->getId();
        $data["timestampGMT"] = isset($data["date"]) ? $data["date"] : gmmktime();

        $sqlite_request = Analytics_Model_Store::getInstance();
        return $sqlite_request->addAppInstallationMetric($data);
    }

    /**
     * @param $data
     * @return array|bool
     */
    public function _opening ($data)
    {
        $data['appId'] = $this->getApplication()->getId();
        $data['startTimestampGMT'] = isset($data["date"]) ? $data["date"] : gmmktime();
        $data['endTimestampGMT'] = isset($data["date"]) ? $data["date"] : gmmktime();

        $sqliteRequest = Analytics_Model_Store::getInstance();
        return $sqliteRequest->addAppLoadedMetric($data);
    }

    /**
     * @param $data
     * @return array|bool
     */
    public function _closing ($data)
    {
        $time = isset($data["date"]) ? $data["date"] : gmmktime();
        $metric_data = ["endTimestampGMT = '" . $time . "'"];
        $sqlite_request = Analytics_Model_Store::getInstance();
        return $sqlite_request->addAppLoadedMetric($metric_data, $data['id']);
    }

    /**
     * @param $data
     * @return bool
     */
    public function _pageopening ($data)
    {
        $data["timestampGMT"] = isset($data["date"]) ? $data["date"] : gmmktime();
        $sqlite_request = Analytics_Model_Store::getInstance();
        return $sqlite_request->addAppPageNavigationMetric($data);
    }

    /**
     * @param $data
     * @return bool
     */
    public function _productsold($data)
    {
        $metric_value = $data;
        unset($metric_value['products']);
        $metric_value["timestampGMT"] = isset($data["date"]) ? $data["date"] : gmmktime();

        foreach ($data['products'] as $product) {
            $metric_value['productId'] = $product['id'];
            $metric_value['categoryId'] = $product['category_id'];
            $metric_value['quantity'] = $product['quantity'];

            $sqlite_request = Analytics_Model_Store::getInstance();
            return $sqlite_request->addAppMcommerceProductSoldMetric($metric_value);
        }
    }
}
