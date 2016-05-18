<?php

class Tip_Model_Tip extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    public function getFeaturePaths($option_value) {

        if(!$this->isCachable()) return array();

        $paths = array();

        $params = array(
            'value_id' => $option_value->getId()
        );
        $paths[] = $option_value->getPath("findall", $params, false);

        return $paths;

    }

}
