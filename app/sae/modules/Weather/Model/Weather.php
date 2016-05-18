<?php
class Weather_Model_Weather extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Weather_Model_Db_Table_Weather';
        return $this;
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

}
