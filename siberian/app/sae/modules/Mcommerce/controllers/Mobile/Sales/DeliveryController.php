<?php

/**
 * Class Mcommerce_Mobile_Sales_DeliveryController
 */
class Mcommerce_Mobile_Sales_DeliveryController extends Mcommerce_Controller_Mobile_Default
{

    /**
     *
     */
    public function findstoreAction()
    {
        try {
            $cart = $this->getCart();
            $store = $cart->getStore();
            $app = $this->getApplication();

            $storeJson = [
                "id" => (integer) $store->getId(),
                "name" => (string) $store->getName(),
                "deliveryMethods" => [],
                "clients_calculate_change" => (boolean) $store->getClientsCalculateChange()
            ];

            /**
             * @var $deliveryMethods Mcommerce_Model_Delivery_Method[]
             */
            $deliveryMethods = $store->getDeliveryMethods();
            foreach ($deliveryMethods as $deliveryMethod) {

                $deliveryMethod->setCart($this->getCart());
                $isAvailable = $deliveryMethod->isAvailable($app);

                if ($isAvailable) {
                    $storeJson["deliveryMethods"][] = [
                        "id" => (integer) $deliveryMethod->getId(),
                        "code" => (string) $deliveryMethod->getCode(),
                        "name" => (string) $deliveryMethod->getName(),
                        "price" => (double) $deliveryMethod->getPrice(),
                        "formattedPrice" => $deliveryMethod->getPrice() > 0 ?
                            $deliveryMethod->getFormattedPrice() : null
                    ];
                } else {
                    // If delivery!
                    if ($deliveryMethod->getCode() === "home_delivery") {
                        $storeJson["deliveryKo"][] = [
                            "id" => (integer) $deliveryMethod->getId(),
                            "code" => (string) $deliveryMethod->getCode(),
                            "name" => (string) $deliveryMethod->getName(),
                            "price" => (double) $deliveryMethod->getPrice(),
                            "formattedPrice" => $deliveryMethod->getPrice() > 0 ?
                                $deliveryMethod->getFormattedPrice() : null
                        ];
                    }
                }
            }

            $payload["store"] = $storeJson;
            $payload["clients_calculate_change"] = (boolean) ($cart->getDeliveryMethod()->getCode() === "home_delivery" &&
                $cart->getStore()->getClientsCalculateChange());
            
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function updateAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $datas= $data["form"];

            $html = [];

            try {

                if(empty($datas['delivery_method_id'])) throw new Exception($this->_('Please choose a delivery method'));
                if(empty($datas['store_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                $store = new Mcommerce_Model_Store();
                $store->find($datas['store_id']);
                $method = $store->getDeliveryMethod($datas['delivery_method_id']);
                $delivery_tax = $store->getTax($method->getTaxId());
                $delivery_tax_rate = $delivery_tax->getRate();

                $data_cart = [
                    "store_id" => $datas['store_id'],
                    "paid_amount" => $datas['paid_amount'] ? $datas['paid_amount'] : null,
                    "delivery_cost" => $method->getPrice(),
                    "delivery_tax_rate" => $delivery_tax_rate,
                    "tip" => $this->getCart()->getTip() // To avoid loss of tip
                ];

                $this->getCart()
                    ->setData($data_cart)
                    ->setDeliveryMethodId($datas['delivery_method_id'])
                    ->save()
                ;

                $html = [
                    'store_id' => $this->getCart()->getStoreId(),
                    'delivery_method_id' => $this->getCart()->getDeliveryMethodId(),
                    'cartId' => $this->getCart()->getId()
                ];
            }
            catch(Exception $e ) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendHtml($html);
        } 

    }
}