<?php

/**
 * Class Mcommerce_Model_Delivery_Method_Homedelivery
 */
class Mcommerce_Model_Delivery_Method_Homedelivery extends Mcommerce_Model_Delivery_Method_Abstract
{

    /**
     * @return bool
     */
    public function isAvailable()
    {
        $store = $this->getMethod()->getStore();
        $cart = $this->getMethod()->getCart();

        if ($store->getId() && $cart->getId()) {
            if ($store->getDeliveryArea() > 0) {
                $addressStore = [
                    'street' => $store->getStreet(),
                    'postcode' => $store->getPostcode(),
                    'city' => $store->getCity(),
                    'country' => $store->getcountry()
                ];
                $geoStore = Siberian_Google_Geocoding::getLatLng($addressStore);
                if (isset($geoStore[0]) && isset($geoStore[1])) {
                    $storeCoordinates = [
                        'latitude' => $geoStore[0],
                        'longitude' => $geoStore[1]
                    ];
                }
                
                if ($cart->getCustomerLatitude() && $cart->getCustomerLongitude()) {
                    $customerCoordinates = [
                        'latitude' => $cart->getCustomerLatitude(),
                        'longitude' => $cart->getCustomerLongitude()
                    ];
                }

                if ($storeCoordinates && $customerCoordinates) {
                    $distance = Siberian_Google_Geocoding::getDistance($storeCoordinates, $customerCoordinates);
                    return $distance <= $store->getDeliveryArea();
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function customerAddressIsRequired()
    {
        return $this->getMethod()->getCart()->getStore()->getDeliveryArea() > 0;
    }

}
