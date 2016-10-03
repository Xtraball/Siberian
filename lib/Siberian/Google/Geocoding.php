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

        /** First search in geocache */
        $geocoding = new Cache_Model_Geocoding();
        $geocoding->find($address, "key");

        if($geocoding->getId()) {
            return array(
                $geocoding->getLatitude(),
                $geocoding->getLongitude(),
            );
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address";
        $raw_response = @file_get_contents($url);
        if($raw_response AND $coordinates_datas = @json_decode($raw_response)) {
            if(!empty($coordinates_datas->results[0]->geometry->location)) {
                $latlng = $coordinates_datas->results[0]->geometry->location;
                $precision = $coordinates_datas->results[0]->geometry->location_type;
                $data = array(
                    !empty($latlng->lat) ? $latlng->lat : '',
                    !empty($latlng->lng) ? $latlng->lng : ''
                );

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
    public static function geoReverse($latitude, $longitude) {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latitude.",".$longitude."&sensor=true";
        $decode = Siberian_Json::decode(file_get_contents($url));

        $locality = "";
        $postal_code = "";

        $result = $decode["results"][0];
        $address_components = $result["address_components"];
        foreach ($address_components as $address_component) {
            $type = $address_component["types"][0];
            if($type == "locality") {
                $locality = $address_component["long_name"];
            }
            if($type == "postal_code") {
                $postal_code = $address_component["long_name"];
            }
        }

        return array(
            "locality" => $locality,
            "postal_code" => $postal_code
        );
    }

    public static function getDistance($coordinates_a, $coordinates_b) {

        $rad = pi() / 180;
        $lat_a = $coordinates_a["latitude"] * $rad;
        $lat_b = $coordinates_b["latitude"] * $rad;
        $lon_a = $coordinates_a["longitude"] * $rad;
        $lon_b = $coordinates_b["longitude"] * $rad;

        $distance = 2 * asin(sqrt(pow(sin(($lat_a-$lat_b)/2) , 2) + cos($lat_a)*cos($lat_b)* pow( sin(($lon_a-$lon_b)/2) , 2)));
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
    public static function getDistanceFormula($lat, $long, $prefix = 'location', $lat_name = 'lat', $long_name = 'lng') {
        return '((((ACOS(SIN(('.$lat.'*PI()/180)) * SIN(('.$prefix.'.'.$lat_name.'*PI()/180)) + COS(('.$lat.'*PI()/180)) * COS(('.$prefix.'.'.$lat_name.'*PI()/180)) * COS((('.$long .' - '.$prefix.'.'.$long_name.')*PI()/180))))*180/PI())*111189.577))';
    }



}

/***
 * EXAMPLE JSON RESPONSE

{
    "results" : [
      {
          "address_components" : [
            {
                "long_name" : "Toulouse",
               "short_name" : "Toulouse",
               "types" : [ "locality", "political" ]
            },
            {
                "long_name" : "Haute-Garonne",
               "short_name" : "Haute-Garonne",
               "types" : [ "administrative_area_level_2", "political" ]
            },
            {
                "long_name" : "Languedoc-Roussillon Midi-Pyrénées",
               "short_name" : "Languedoc-Roussillon Midi-Pyrénées",
               "types" : [ "administrative_area_level_1", "political" ]
            },
            {
                "long_name" : "France",
               "short_name" : "FR",
               "types" : [ "country", "political" ]
            }
         ],
         "formatted_address" : "Toulouse, France",
         "geometry" : {
          "bounds" : {
              "northeast" : {
                  "lat" : 43.6686919,
                  "lng" : 1.515354
               },
               "southwest" : {
                  "lat" : 43.532708,
                  "lng" : 1.3503279
               }
            },
            "location" : {
              "lat" : 43.604652,
               "lng" : 1.444209
            },
            "location_type" : "APPROXIMATE",
            "viewport" : {
              "northeast" : {
                  "lat" : 43.6686919,
                  "lng" : 1.515354
               },
               "southwest" : {
                  "lat" : 43.532708,
                  "lng" : 1.3503279
               }
            }
         },
         "place_id" : "ChIJ_1J17G-7rhIRMBBBL5z2BgQ",
         "types" : [ "locality", "political" ]
      }
   ],
   "status" : "OK"
}

 * */