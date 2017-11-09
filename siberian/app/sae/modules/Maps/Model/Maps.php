<?php
class Maps_Model_Maps extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Maps_Model_Db_Table_Maps';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "maps-view",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = array(
            "collection"    => array(),
            "page_title"    => $option_value->getTabbarName(),
            "icon_url"      => Core_Model_Lib_Image::sGetImage("maps/")
        );

        if($this->getId()) {

            /** Fallback/Fix for empty lat/lng */
            $lat = $this->getLatitude();
            $lng = $this->getLongitude();
            if(empty($lat) && empty($lng)) {
                $geo = Siberian_Google_Geocoding::getLatLng($this->getAddress());
                $this->setLatitude($geo[0]);
                $this->setLongitude($geo[1]);
            }

            $payload["collection"] = $this->getData();
        }

        return $payload;

    }

    public function copyTo($option) {

        $this->setId(null)->setValueId($option->getId())->save();
        return $this;

    }

    public function getFeaturePaths($option_value) {
        $paths = parent::getFeaturePaths($option_value);

        $maps_icons = array("car.png", "walk.png", "bus.png", "error.png");
        $color = str_ireplace("#", "", $this->getApplication()->getBlock("list_item")->getColor());

        foreach($maps_icons as $maps_icon) {
            $btoa_image = base64_encode(Core_Model_Directory::getDesignPath(false) . '/images/maps/' . $maps_icon);

            $params = array(
                "color" => $color,
                "path" => $btoa_image
            );
            $paths[] = $this->getPath("/template/block/colorize/", $params);
        }

        return $paths;
    }

}
