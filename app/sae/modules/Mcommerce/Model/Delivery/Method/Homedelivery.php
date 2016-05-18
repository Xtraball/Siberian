<?php

class Mcommerce_Model_Delivery_Method_Homedelivery extends Mcommerce_Model_Delivery_Method_Abstract {

    public function isAvailable() {

        $store = $this->getMethod()->getStore();
        $cart = $this->getMethod()->getCart();

        if($store->getId() AND $cart->getId()) {

            if($store->getDeliveryArea() > 0) {

                if($store->getLatitude() AND $store->getLongitude() AND $cart->getCustomerLatitude() AND $cart->getCustomerLongitude()) {
                    $store_coordinates = array('latitude' => $store->getLatitude(), 'longitude' => $store->getLongitude());
                    $customer_coordinates = array('latitude' => $cart->getCustomerLatitude(), 'longitude' => $cart->getCustomerLongitude());
                    if($store_coordinates AND $customer_coordinates) {
                        $distance = Siberian_Google_Geocoding::getDistance($store_coordinates, $customer_coordinates);
                        return $distance <= $store->getDeliveryArea();
                    }
                }
            } else {
                return true;
            }
        }

        return false;
    }

    public function isFree() {
        return false;
    }

    public function customerAddressIsRequired() {
        return $this->getMethod()->getCart()->getStore()->getDeliveryArea() > 0;
    }

}
