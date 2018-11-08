<?php

/**
 * Class Siberian_Google_Geocoding
 */
class Siberian_Google_Geocoding
{
    /**
     * @param $address
     * @return array
     */
    public static function getLatLng($address, $apiKey = null)
    {

        if (!empty($address["address"])) {
            $address = str_replace(PHP_EOL, " ", $address["address"]);
        } else {
            $address = join(', ', [
                $address['street'],
                $address['postcode'],
                $address['city'],
                !empty($address['country']) ? $address['country'] : null
            ]);
        }
        $address = str_replace(' ', '+', $address);
        $data = ['', ''];

        /** First search in geocache */
        $geocoding = new Cache_Model_Geocoding();
        $geocoding->find($address, "key");

        if ($geocoding->getId()) {
            return [
                $geocoding->getLatitude(),
                $geocoding->getLongitude(),
            ];
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address&key=$apiKey";
        $raw_response = @file_get_contents($url);
        if ($raw_response && $coordinates_datas = @json_decode($raw_response)) {
            if (!empty($coordinates_datas->results[0]->geometry->location)) {
                $latlng = $coordinates_datas->results[0]->geometry->location;
                $precision = $coordinates_datas->results[0]->geometry->location_type;
                $data = [
                    !empty($latlng->lat) ? $latlng->lat : '',
                    !empty($latlng->lng) ? $latlng->lng : ''
                ];

                $geocoding->setKey($address);
                $geocoding->setRawResult($raw_response);
                $geocoding->setLatitude($data[0]);
                $geocoding->setLongitude($data[1]);
                $geocoding->setPrecision($precision);
                $geocoding->save();
            }
        }

        return $data;

    }

    /**
     * @param $latitude
     * @param $longitude
     * @return array
     */
    public static function geoReverse($latitude, $longitude, $apiKey = null)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latitude .
            ',' . $longitude . '&sensor=true&key=' . $apiKey;
        $decode = Siberian_Json::decode(file_get_contents($url));

        $locality = '';
        $postal_code = '';
        $country = '';
        $country_code = '';

        $result = $decode['results'][0];
        $address_components = $result['address_components'];
        foreach ($address_components as $address_component) {
            $type = $address_component['types'][0];
            if ($type === 'locality') {
                $locality = $address_component['long_name'];
            }
            if ($type === 'postal_code') {
                $postal_code = $address_component['long_name'];
            }
            if ($type === 'country') {
                $country = $address_component['long_name'];
                $country_code = $address_component['short_name'];
            }
        }

        return [
            'locality' => $locality,
            'postal_code' => $postal_code,
            'country' => $country,
            'country_code' => $country_code,
        ];
    }

    /**
     * @param $coordinates_a
     * @param $coordinates_b
     * @return float|int
     */
    public static function getDistance($coordinates_a, $coordinates_b)
    {
        $rad = pi() / 180;
        $lat_a = $coordinates_a["latitude"] * $rad;
        $lat_b = $coordinates_b["latitude"] * $rad;
        $lon_a = $coordinates_a["longitude"] * $rad;
        $lon_b = $coordinates_b["longitude"] * $rad;

        $distance = 2 * asin(sqrt(pow(sin(($lat_a - $lat_b) / 2), 2) + cos($lat_a) * cos($lat_b) *
                pow(sin(($lon_a - $lon_b) / 2), 2)));
        $distance *= 6371;

        return $distance;
    }

    /**
     * Build distance calculation for mysql
     *
     * @param $lat
     * @param $long
     * @param string $prefix
     * @param string $lat_name
     * @param string $long_name
     * @return string
     */
    public static function getDistanceFormula($lat, $long, $prefix = 'location', $lat_name = 'lat', $long_name = 'lng')
    {
        return '((((ACOS(SIN((' . $lat . '*PI()/180)) * SIN((' . $prefix . '.' . $lat_name . '*PI()/180)) + COS((' .
            $lat . '*PI()/180)) * COS((' . $prefix . '.' . $lat_name . '*PI()/180)) * COS(((' . $long . ' - ' .
            $prefix . '.' . $long_name . ')*PI()/180))))*180/PI())*111189.577))';
    }

    /**
     * @param null $apiKey
     * @throws \Siberian\Exception
     */
    public static function testApiKey($apiKey = null)
    {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            throw new \Siberian\Exception('#807-101: ' . __('Missing and/or empty API key.'));
        }

        $response = \Siberian_Request::get("https://maps.googleapis.com/maps/api/geocode/json", [
            "sensor" => "false",
            "address" => "12 Avenue de Paradis, Paris, France",
            "key" => $apiKey,
        ]);

        if (empty($response)) {
            throw new \Siberian\Exception('#807-102: ' . __('Something went wrong with the API.'));
        }

        $result = \Siberian_Json::decode($response);

        if (!array_key_exists('status', $result)) {
            throw new \Siberian\Exception('#807-103: ' . __('Something went wrong with the API.'));
        }

        if ($result['status'] !== 'OK') {
            throw new \Siberian\Exception('#807-104: ' . $result['error_message']);
        }

        return true;
    }


}