<?php

/**
 * Class Weather_Mobile_ViewController
 */
class Weather_Mobile_ViewController extends Application_Controller_Mobile_Default
{
    /**
     * @var string
     */
    public static $owmEndpoint = "https://api.openweathermap.org/data/2.5/onecall";

    /**
     *
     */
    public function findAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);

            if (!$valueId) {
                throw new \Siberian\Exception(__("An error occurred during process. Please try again later."));
            }

            $weather = (new Weather_Model_Weather())
                ->find($valueId, "value_id");

            $payload = [
                "success" => true,
                "page_title" => (string) $this->getCurrentOptionValue()->getTabbarName(),
                "unit" => (string) strtoupper($weather->getUnit()),
                "units" => (string) ($weather->getUnit() === "c") ? "metric" : "imperial",
                "country" => (string) $weather->getCountryCode(),
                "city" => (string) $weather->getCity(),
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function getweatherAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();
            $application = $this->getApplication();
            $googleKey = $application->getGooglemapsKey();
            $appId = $application->getId();
            
            if (empty($params)) {
                throw new \Siberian\Exception(__("Missing params"));
            }

            $data = [
                "units" => $params["units"],
                "exclude" => "minutely,hourly,alerts",
                "appid" => $application->getOwmKey(),
            ];

            $isCoord = isset($params["lat"]);

            if (!$isCoord) {
                $parts = explode(',', $params['q']);
                $gParams = [
                    'address' => $parts[0],
                    'country' => $parts[1],
                ];
                $reversed = Siberian_Google_Geocoding::getLatLng($gParams, $googleKey);
                $data["lat"] = $reversed[0];
                $data["lon"] = $reversed[1];
                $place = $params['q'];
            } else {
                $reversed = Siberian_Google_Geocoding::geoReverse($params["lat"], $params["lon"], $googleKey);
                $data["lat"] = $params["lat"];
                $data["lon"] = $params["lon"];
                $place = $reversed['locality'] . ', '. $reversed['country'];
            }

            $slugged = slugify($data["lat"] . "-" . $data["lon"]);

            $units = $params["units"];
            $cacheIdWeather = preg_replace(
                "/[^a-zA-Z0-9_]/",
                "_",
                "weather2_{$appId}_{$slugged}_{$units}");

            $result = $this->cache->load($cacheIdWeather);
            if (!$result) {

                $onecall = Siberian_Request::get(self::$owmEndpoint, $data);
                $responseOnecall = Siberian_Json::decode($onecall);

                // Transfer error code if cod match 40x
                if (strpos($responseOnecall['cod'], '40') === 0) {
                    throw new \Siberian\Exception($responseOnecall["message"]);
                }

                $payload = [
                    "success" => true,
                    "cache" => "MISS",
                    "place" => $place,
                    "weather" => $responseOnecall,
                ];

                // Cache for 1 hour! (free data is refreshed every 2 hours)
                $this->cache->save($payload, $cacheIdWeather, ["weather2"], 3600);
            } else {
                $payload = $result;

                $payload["cache"] = "HIT";
            }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }
}