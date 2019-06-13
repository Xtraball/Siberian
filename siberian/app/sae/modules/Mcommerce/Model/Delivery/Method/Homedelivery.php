<?php

use Siberian_Google_Geocoding as Geocoding;

/**
 * Class Mcommerce_Model_Delivery_Method_Homedelivery
 */
class Mcommerce_Model_Delivery_Method_Homedelivery extends Mcommerce_Model_Delivery_Method_Abstract
{

    /**
     * @return bool
     */
    public function isAvailable($application)
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

                $geoStore = Geocoding::getLatLng($addressStore, $application->getGooglemapsKey());

                $hasGeostore = (isset($geoStore[0]) && isset($geoStore[1]));
                $hasCustomerGeo = ($cart->getCustomerLatitude() && $cart->getCustomerLongitude());

                if ($hasGeostore && $hasCustomerGeo) {
                    $storeCoordinates = [
                        "latitude" => $geoStore[0],
                        "longitude" => $geoStore[1]
                    ];

                    $customerCoordinates = [
                        "latitude" => $cart->getCustomerLatitude(),
                        "longitude" => $cart->getCustomerLongitude()
                    ];

                    $distance = Geocoding::getDistance($storeCoordinates, $customerCoordinates);
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
