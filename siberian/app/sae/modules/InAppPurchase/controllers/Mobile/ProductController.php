<?php

use InAppPurchase\Model\Product;

/**
 * Class InAppPurchase_Mobile_ProductController
 */
class InAppPurchase_Mobile_ProductController extends Application_Controller_Mobile_Default
{
    /**
     * @throws Zend_Controller_Response_Exception
     * @route /inapppurchase/mobile_product/all
     */
    public function allAction()
    {
        try {
            $application = $this->getApplication();
            $appId = $application->getId();

            $products = (new Product())->findAll([
                'app_id = ?' => $appId
            ]);

            $collection = [];
            foreach ($products as $product) {
                $data = $product->getData();
                $collection[] = $data;
            }

            $payload = [
                'success' => true,
                'collection' => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

}
