<?php

class Privacypolicy_Model_Privacypolicy extends Core_Model_Default {

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "privacy-policy",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }
}