<?php

/**
 * Class Wordpress2_Model_Wordpress
 */
class Wordpress2_Model_Wordpress extends Core_Model_Default {

    /**
     * Wordpress2_Model_Wordpress constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Wordpress2_Model_Db_Table_Wordpress';
        return $this;
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris ($optionValue)
    {
        $featureUrl = __url("/wordpress2/mobile_list/index", [
            "value_id" => $this->getValueId(),
        ]);
        $featurePath = __path("/wordpress2/mobile_list/index", [
            "value_id" => $this->getValueId(),
        ]);


        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }
}