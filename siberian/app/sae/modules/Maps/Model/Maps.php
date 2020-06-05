<?php

/**
 * Class Maps_Model_Maps
 */
class Maps_Model_Maps extends Core_Model_Default
{

    /**
     * Maps_Model_Maps constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Maps_Model_Db_Table_Maps';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id)
    {

        $in_app_states = [
            [
                "state" => "maps-view",
                "offline" => false,
                "params" => [
                    "value_id" => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value = null)
    {

        $payload = [
            "collection" => [],
            "page_title" => $option_value->getTabbarName(),
            "icon_url" => Core_Model_Lib_Image::sGetImage("maps/")
        ];

        if ($this->getId()) {

            /** Fallback/Fix for empty lat/lng */
            $lat = $this->getLatitude();
            $lng = $this->getLongitude();
            if (empty($lat) && empty($lng)) {
                $geo = Siberian_Google_Geocoding::getLatLng($this->getAddress(), $this->getApplication()->getGooglemapsKey());
                $this->setLatitude($geo[0]);
                $this->setLongitude($geo[1]);
            }

            $payload["collection"] = $this->getData();
        }

        return $payload;

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

        $maps_icons = ["car.png", "walk.png", "bus.png", "error.png"];
        $color = str_ireplace("#", "", $this->getApplication()->getBlock("list_item")->getColor());

        foreach ($maps_icons as $maps_icon) {
            $btoa_image = base64_encode(Core_Model_Directory::getDesignPath(false) . '/images/maps/' . $maps_icon);

            $params = [
                "color" => $color,
                "path" => $btoa_image
            ];
            $paths[] = $this->getPath("/template/block/colorize/", $params);
        }

        return $paths;
    }

}
