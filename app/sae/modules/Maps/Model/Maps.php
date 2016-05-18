<?php
class Maps_Model_Maps extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Maps_Model_Db_Table_Maps';
        return $this;
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
