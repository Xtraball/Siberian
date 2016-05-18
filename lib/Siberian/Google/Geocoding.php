<?php

class Siberian_Google_Geocoding
{

    public static function getLatLng($address) {

        if(!empty($address["address"])) {
            $address = str_replace(PHP_EOL, " ", $address["address"]);
        } else {
            $address = join(', ', array(
                $address['street'],
                $address['postcode'],
                $address['city'],
                !empty($address['country']) ? $address['country'] : null
            ));
        }
        $address = str_replace(' ', '+', $address);
        $data = array('', '');

        $url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address";
        $coordinates_datas = @file_get_contents($url);
        if($coordinates_datas = @file_get_contents($url) AND $coordinates_datas = @json_decode($coordinates_datas)) {
            if(!empty($coordinates_datas->results[0]->geometry->location)) {
                $latlng = $coordinates_datas->results[0]->geometry->location;
                $data = array(
                    !empty($latlng->lat) ? $latlng->lat : '',
                    !empty($latlng->lng) ? $latlng->lng : ''
                );
            }
        }

        return $data;

    }

    public static function getDistance($coordinates_a, $coordinates_b) {

        $rad = pi() / 180;
        $lat_a = $coordinates_a['latitude'] * $rad;
        $lat_b = $coordinates_b['latitude'] * $rad;
        $lon_a = $coordinates_a['longitude'] * $rad;
        $lon_b = $coordinates_b['longitude'] * $rad;

        $distance = 2 * asin(sqrt(pow(sin(($lat_a-$lat_b)/2) , 2) + cos($lat_a)*cos($lat_b)* pow( sin(($lon_a-$lon_b)/2) , 2)));
        $distance *= 6371;

        return $distance;

    }

}
