<?php

use Siberian\Json;

/**
 * Class Siberian_Google_Geocoding
 */
class Siberian_Google_Geocoding
{
    /**
     * @paaram $address
     * @paaram $apiKey
     * @return array
     */
    public static function getLatLng($address, $apiKey = null)
    {
        $refresh = false;
        if (array_key_exists("refresh", $address)) {
            $refresh = true;
        }

        if (!empty($address["address"])) {
            $address = str_replace(PHP_EOL, " ", $address["address"]);
        } else {
            $address = implode_polyfill(', ', [
                $address['street'],
                $address['postcode'],
                $address['city'],
                !empty($address['country']) ? $address['country'] : null
            ]);
        }
        $address = str_replace(' ', '+', $address);
        $data = ['', ''];

        /** First search in geocache (or not if refresh) */
        $geoCoding = new Cache_Model_Geocoding();
        if (!$refresh) {
            $geoCoding->find($address, "key");

            if ($geoCoding->getId()) {
                return [
                    $geoCoding->getLatitude(),
                    $geoCoding->getLongitude(),
                ];
            }
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address&key=$apiKey";
        $raw_response = file_get_contents($url);
        if ($raw_response && $coordinates_datas = @json_decode($raw_response)) {
            if (!empty($coordinates_datas->results[0]->geometry->location)) {
                $latlng = $coordinates_datas->results[0]->geometry->location;
                $precision = $coordinates_datas->results[0]->geometry->location_type;
                $data = [
                    !empty($latlng->lat) ? $latlng->lat : '',
                    !empty($latlng->lng) ? $latlng->lng : ''
                ];

                $geoCoding->setKey($address);
                $geoCoding->setRawResult($raw_response);
                $geoCoding->setLatitude($data[0]);
                $geoCoding->setLongitude($data[1]);
                $geoCoding->setPrecision($precision);
                $geoCoding->save();
            }
        }

        return $data;
    }

    /**
     * @param $address
     * @param null $apiKey
     * @return bool|Cache_Model_Geocoding
     */
    public static function validateAddress($address, $apiKey = null)
    {
        $refresh = false;
        if (array_key_exists("refresh", $address)) {
            $refresh = true;
        }

        if (!empty($address["address"])) {
            $address = str_replace(PHP_EOL, " ", $address["address"]);
        } else {
            $address = join(", ", [
                $address["street"],
                $address["postcode"],
                $address["city"],
                !empty($address["country"]) ? $address["country"] : null
            ]);
        }
        $address = str_replace(" ", "+", $address);

        /** First search in geocache (or not if refresh) */
        $geoCoding = new Cache_Model_Geocoding();
        if (!$refresh) {
            $geoCoding->find($address, "key");

            if ($geoCoding->getId()) {
                return $geoCoding;
            }
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address&key=$apiKey";
        $rawResponse = file_get_contents($url);
        if ($rawResponse && $coordinatesData = @json_decode($rawResponse)) {
            if (!empty($coordinatesData->results[0]->geometry->location)) {
                $latlng = $coordinatesData->results[0]->geometry->location;
                $precision = $coordinatesData->results[0]->geometry->location_type;
                $data = [
                    !empty($latlng->lat) ? $latlng->lat : '',
                    !empty($latlng->lng) ? $latlng->lng : ''
                ];

                $geoCoding->setKey($address);
                $geoCoding->setRawResult($rawResponse);
                $geoCoding->setLatitude($data[0]);
                $geoCoding->setLongitude($data[1]);
                $geoCoding->setPrecision($precision);
                $geoCoding->save();

                return $geoCoding;
            }
        }

        return false;
    }

    /**
     * Utility method to extract address components from google answer!
     *
     * @param $rawJson
     * @return array
     */
    public static function rawToParts ($rawJson)
    {
        $parts = Json::decode($rawJson);
        $components = $parts["results"][0]["address_components"];

        $simpleParts = [];
        foreach ($components as $component) {
            $types = $component["types"];
            foreach ($types as $type) {
                if ($type === "political") {
                    continue;
                }
                $simpleParts[$type] = $component["long_name"];
            }
        }

        return $simpleParts;
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
     * @return bool
     * @throws \Siberian\Exception
     */
    public static function testApiKey($apiKey = null)
    {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            throw new \Siberian\Exception('#807-101 [GoogleMaps]: ' . __('Missing and/or empty API key.'));
        }

        $response = \Siberian_Request::get("https://maps.googleapis.com/maps/api/geocode/json", [
            "sensor" => "false",
            "address" => "12 Avenue de Paradis, Paris, France",
            "key" => $apiKey,
        ]);

        if (empty($response)) {
            throw new \Siberian\Exception('#807-102 [GoogleMaps]: ' . __('Something went wrong with the API.'));
        }

        $result = \Siberian_Json::decode($response);

        if (!array_key_exists('status', $result)) {
            throw new \Siberian\Exception('#807-103 [GoogleMaps]: ' . __('Something went wrong with the API.'));
        }

        if ($result['status'] !== 'OK') {
            throw new \Siberian\Exception('#807-104 [GoogleMaps]: ' . $result['error_message']);
        }

        return true;
    }

    /**
     * Generates a static map image!
     *
     * @param null $apiKey
     * @param array $options
     * @return string
     */
    public static function mapStatic ($apiKey = null, $options = [])
    {
        $endpoint = "https://maps.googleapis.com/maps/api/staticmap";
        $parts = [
            "size" => "560x240",
            "key" => $apiKey,
        ];

        $_m = [];
        if (array_key_exists("markers", $options)) {
            $markers = $options["markers"];
            unset($options["markers"]);
            foreach ($markers as $marker) {
                $_m[] = "markers={$marker}";
            }
        }
        if (!empty($_m)) {
            $_m = "&" . join("&", $_m);
        } else {
            $_m = "";
        }

        $parts = array_merge($parts, $options);
        $builtUri = $endpoint . "?" . http_build_query($parts) . $_m;

        return $builtUri;
    }


}
