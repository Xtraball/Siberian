<?php

/**
 * Class Weather_Mobile_ViewController
 */
class Weather_Mobile_ViewController extends Application_Controller_Mobile_Default
{
    /**
     * @var string
     */
    public static $owmCurrentEndpoint = "https://api.openweathermap.org/data/2.5/weather";

    /**
     * @var string
     */
    public static $owmForecastEndpoint = "https://api.openweathermap.org/data/2.5/forecast";

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
            $appId = $application->getId();
            
            if (empty($params)) {
                throw new \Siberian\Exception(__("Missing params"));
            }

            $data = [
                "units" => $params["units"],
                "appid" => $application->getOwmKey(),
            ];

            $isCoord = isset($params["lat"]);
            $isZip = isset($params["zip"]);
            if ($isCoord) {
                $data["lat"] = $params["lat"];
                $data["lon"] = $params["lon"];
                $slugged = slugify($data["lat"] . "-" . $data["lon"]);
            } /**else if ($isZip) {
                $data["zip"] = $params["zip"];
                $slugged = slugify($data["zip"]);
            } */else {
                $data["q"] = $params["q"];
                $slugged = slugify($data["q"]);
            }

            $units = $params["units"];
            $cacheIdWeather = preg_replace(
                "/[^a-zA-Z0-9_]/",
                "_",
                "weather_{$appId}_{$slugged}_{$units}");

            $result = $this->cache->load($cacheIdWeather);
            if (!$result) {

                $weather = Siberian_Request::get(self::$owmCurrentEndpoint, $data);
                $responseWeather = Siberian_Json::decode($weather);

                // Transfer error code if cod match 40x
                if (strpos($responseWeather["cod"], "40") === 0) {
                    throw new \Siberian\Exception($responseWeather["message"]);
                }

                $forecast = Siberian_Request::get(self::$owmForecastEndpoint, $data);
                $responseForecast = Siberian_Json::decode($forecast);

                // Transfer error code if cod match 40x
                if (strpos($responseForecast["cod"], "40") === 0) {
                    throw new \Siberian\Exception($responseForecast["message"]);
                }

                $payload = [
                    "success" => true,
                    "cache" => "MISS",
                    "weather" => $responseWeather,
                    "forecast" => $responseForecast,
                ];

                // Cache for 1 hour! (free data is refreshed every 2 hours
                $this->cache->save($payload, $cacheIdWeather, ["weather"], 3600);
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