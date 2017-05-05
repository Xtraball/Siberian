<?php
$loyalty_patch = System_Model_Config::getValueFor("loyalty_patch_4.8.12");
if(empty($loyalty_patch)) {
    try {
        $passwords = new LoyaltyCard_Model_Password();

        $passwords = $passwords->findAll();

        foreach ($passwords as $password) {
            $cpt = 0;
            $option_values = new Application_Model_Option_Value();
            $option_values = $option_values->findAllWithOptionsInfos(array("app_id" => $password->getAppId(), "code" => "loyalty"));

            if ($option_values->count() > 0) {
                foreach ($option_values as $option) {
                    $password->setData("value_id", intval($option->getValueId()));
                    if ($cpt > 0) {
                        $password->unsData("password_id")->unsData("id");
                    }
                    $password->save();
                    $cpt++;
                }
            }
        }
        System_Model_Config::setValueFor("loyalty_patch_4.8.12", time());
    } catch (Exception $e) {}
}