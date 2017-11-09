<?php

class Front_View_Mobile_Js extends Core_View_Mobile_Default {

    public function getAdmobSettings() {

        $application = $this->getApplication();

        $subscription = null;
        $pe_use_ads = null;
        if($this->isPe()) {
            $subscription = $application->getSubscription()->getSubscription();
            $pe_use_ads = $subscription->getUseAds();
        }

        $device = $this->getDevice()->isIosdevice() ? $application->getDevice(1) : $application->getDevice(2);

        if($this->getApplication()->getOwnerUseAds()) {

            $settings = array(
                "admob_id" => $device->getOwnerAdmobId(),
                "admob_type" => $device->getOwnerAdmobType(),
                "use_ads" => true
            );

        } else {
            if($pe_use_ads) {

                $settings = array(
                    "admob_id" => System_Model_Config::getValueFor("application_".$device->getType()->getOsName()."_owner_admob_id"),
                    "admob_type" => System_Model_Config::getValueFor("application_".$device->getType()->getOsName()."_owner_admob_type"),
                    "use_ads" => true
                );

            } else {

                if (System_Model_Config::getValueFor("application_owner_use_ads")) {

                    $settings = array(
                        "admob_id" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_id"),
                        "admob_type" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_type"),
                        "use_ads" => true
                    );

                } else {

                    $settings = array(
                        "admob_id" => $device->getAdmobId(),
                        "admob_type" => $device->getAdmobType(),
                        "use_ads" => $application->getUseAds()
                    );

                }
            }
        }

        return $settings;
    }

}