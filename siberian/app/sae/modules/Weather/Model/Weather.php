<?php
class Weather_Model_Weather extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Weather_Model_Db_Table_Weather';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "weather-view",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param null $option_value
     * @return array
     */
    public function getEmbedPayload($option_value = null) {

        $payload = array(
            "collection"    => array(),
            "page_title"    => $option_value->getTabbarName(),
            "icon_url"      => $this->_getImage("weather/")
        );

        if($this->getId()) {
            $payload["collection"] = $this->getData();
        }

        return $payload;
    }

    protected function _getImage($name, $base = false) {

        if(file_exists(Core_Model_Directory::getDesignPath(true) . '/images/' . $name)) {
            return Core_Model_Directory::getDesignPath($base).'/images/'.$name;
        }
        else if(file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);
        }

        return "";

    }

    public function copyTo($option) {

        $this->setId(null)->setValueId($option->getId())->save();
        return $this;

    }

    public function getFeaturePaths($option_value) {
        $paths = parent::getFeaturePaths($option_value);

        $weather_icons = array("weather_3200.png", "wind.png", "atmosphere.png", "astronomy.png");
        $color = str_ireplace("#", "", $this->getApplication()->getBlock("list_item")->getColor());

        foreach($weather_icons as $weather_icon) {
            $btoa_image = base64_encode(Core_Model_Directory::getDesignPath(false) . '/images/weather/' . $weather_icon);

            $params = array(
                "color" => $color,
                "path" => $btoa_image
            );
            $paths[] = $this->getPath("/template/block/colorize/", $params);
        }

        return $paths;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $weather_model = new Weather_Model_Weather();
            $weather = $weather_model->find($value_id, "value_id");

            $dataset = array(
                "option" => $current_option->forYaml(),
                "weather" => $weather->getData(),
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
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
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if(isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            if(isset($dataset["weather"])) {
                $new_weather = new Weather_Model_Weather();
                $new_weather
                    ->setData($dataset["weather"])
                    ->setData("value_id", $application_option->getId())
                    ->unsData("id")
                    ->unsData("weather_id")
                    ->save()
                ;
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }

}
