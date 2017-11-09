<?php
class Codescan_Model_Codescan extends Core_Model_Default {

	protected $_is_cacheable = true;
	
    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "codescan",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

}
