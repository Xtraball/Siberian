<?php

/**
 * Class Weather_Model_Weather
 */
class Weather_Model_Weather extends Core_Model_Default
{
    /**
     * @var string
     */
    protected $_db_table = Weather_Model_Db_Table_Weather::class;

    /**
     * @return array
     */
    public function getInappStates($valueId)
    {

        return [
            [
                'state' => 'weather-view',
                'offline' => false,
                'params' => [
                    'value_id' => $valueId,
                ],
            ],
        ];
    }

    /**
     * @param null $optionValue
     * @return array
     */
    public function getEmbedPayload($optionValue = null)
    {
        if ($this->getId()) {
            $payload = [
                "page_title" => (string) $optionValue->getTabbarName(),
                "unit" => (string) strtoupper($this->getUnit()),
                "units" => (string) ($this->getUnit() === "c") ? "metric" : "imperial",
                "country" => (string) $this->getCountryCode(),
                "city" => (string) $this->getCity(),
            ];
        } else {
            return false;
        }

        return $payload;
    }

    /**
     * @param $name
     * @param bool $base
     * @return string
     * @throws Zend_Exception
     */
    protected function _getImage($name, $base = false)
    {

        if (file_exists(Core_Model_Directory::getDesignPath(true) . '/images/' . $name)) {
            return Core_Model_Directory::getDesignPath($base) . '/images/' . $name;
        } else if (file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);
        }

        return "";

    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option, $parent_id = null)
    {

        $this->setId(null)->setValueId($option->getId())->save();
        return $this;

    }

    /**
     * @param $option_value
     * @return array|string[]
     * @throws Zend_Exception
     */
    public function getFeaturePaths($option_value)
    {
        $paths = parent::getFeaturePaths($option_value);

        $weather_icons = ["weather_3200.png", "wind.png", "atmosphere.png", "astronomy.png"];
        $color = str_ireplace("#", "", $this->getApplication()->getBlock("list_item")->getColor());

        foreach ($weather_icons as $weather_icon) {
            $btoa_image = base64_encode(Core_Model_Directory::getDesignPath(false) . '/images/weather/' . $weather_icon);

            $params = [
                "color" => $color,
                "path" => $btoa_image
            ];
            $paths[] = $this->getPath("/template/block/colorize/", $params);
        }

        return $paths;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null)
    {
        if ($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $weather_model = new Weather_Model_Weather();
            $weather = $weather_model->find($value_id, "value_id");

            $dataset = [
                "option" => $current_option->forYaml(),
                "weather" => $weather->getData(),
            ];

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch (Exception $e) {
                throw new Exception("#089-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#089-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path)
    {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch (Exception $e) {
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if (isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save();

            if (isset($dataset["weather"])) {
                $new_weather = new Weather_Model_Weather();
                $new_weather
                    ->setData($dataset["weather"])
                    ->setData("value_id", $application_option->getId())
                    ->unsData("id")
                    ->unsData("weather_id")
                    ->save();
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }

    /**
     * Testing OWM API Key on saving!
     *
     * @param $key
     * @throws \Siberian\Exception
     */
    public static function testApiKey ($key)
    {
        $request = \Siberian_Request::get("https://api.openweathermap.org/data/2.5/weather?q=London&appid={$key}");
        $result = \Siberian_Json::decode($request);

        // Transfer error code if cod match 40x
        if (strpos($result["cod"], "40") === 0) {
            throw new \Siberian\Exception(__("OpenWeatherMap") . "<br />" . $result["message"]);
        }

        if (\Siberian_Request::$statusCode != "200") {
            throw new \Siberian\Exception(__(__("OpenWeatherMap") . "<br />" . "We were unable to communicate with the OpenWeatherMap API! Please try again later!"));
        }
    }

}
