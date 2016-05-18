<?php

class Mcommerce_Mobile_Sales_StorechoiceController extends Mcommerce_Controller_Mobile_Default {

    public function findAction() {
        $option = $this->getCurrentOptionValue();

        $mcommerce = $option->getObject();
        $stores = $mcommerce->getStores();
        $cart = $this->getCart();

        $html = array("stores" => array());
        foreach ($stores as $store) {

            $storeJson = array(
                "id" => $store->getId(),
                "name" => $store->getName(),
                "formatted_min_amount" => $store->getFormattedMinAmount(),
                "min_amount" => $store->getMinAmount(),
                "error_message" => $this->_("Unable to proceed to checkout the minimum order amount is %s", $store->getFormattedMinAmount())
            );
            $html["stores"][] = $storeJson;
        }
        $html["cart_amount"] = $cart->getSubtotalInclTax();
        $html["store_id"] = $cart->getStoreId();

        $this->_sendHtml($html);

    }

    public function updateAction() {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                if (empty($data['store_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                $data_cart = array(
                    "store_id" => $data['store_id']
                );

                $this->getCart()
                    ->setData($data_cart)
                    ->save();

                $html = array(
                    'store_id' => $this->getCart()->getStoreId()
                );

            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);
        }
    }
}