<?php

class Front_MobileController extends Application_Controller_Mobile_Default {

    public function loadAction() {

        $application = $this->getApplication();
        $customer_id = $this->getSession()->getCustomerId();

        $this->__refreshFBToken($this->getSession()->getCustomer());

        $data = array(
            "css" => $this->getRequest()->getBaseUrl().Template_Model_Design::getCssPath($application)."?t=".time(),
            "customer" => array(
                "id" => $customer_id,
                "can_connect_with_facebook" => !!$application->getFacebookId(),
                "can_access_locked_features" => $customer_id && $this->getSession()->getCustomer()->canAccessLockedFeatures(),
                "token" => Zend_Session::getId()
            ),
            "application" => array(
                "id" => $application->getId(),
                "name" => $application->getName(),
                "is_locked" => $application->requireToBeLoggedIn(),
                "is_bo_locked" => $application->getIsLocked(),
                "colors" => array(
                    "header" => array(
                        "backgroundColor" => $application->getBlock("header")->getBackgroundColorRGB(),
                        "color" => $application->getBlock("header")->getColorRGB()
                    ),
                    "background" => array(
                        "backgroundColor" => $application->getBlock("background")->getBackgroundColor(),
                        "color" => $application->getBlock("background")->getColor()
                    ),
                    "list_item" => array(
                        "color" => $application->getBlock("list_item")->getColor()
                    )
                ),
                "admob" => $this->__getAdmobSettings(),
                "facebook" => array(
                    "id" => $application->getFacebookId(),
                    "scope" => Customer_Model_Customer_Type_Facebook::getScope()
                )
            )
        );

        $this->_sendHtml($data);

    }

    public function styleAction() {
        $html = $this->getLayout()->addPartial('style', 'core_view_mobile_default', 'page/css.phtml')->toHtml();
        $this->getLayout()->setHtml($html);
    }

    public function backgroundimageAction() {

        $urls = array("standard" => "", "hd" => "", "tablet" => "");
        $option = $this->getCurrentOptionValue();

        if($this->getRequest()->getParam("value_id") == "home") {
            $urls = array(
                "standard" => $this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl(),
                "hd" => $this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("hd"),
                "tablet" => $this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("tablet")
            );
        } else if($option->hasBackgroundImage() AND $option->getBackgroundImage() != "no-image") {
            $urls = array(
                "standard" => $this->getRequest()->getBaseUrl().$option->getBackgroundImageUrl(),
                "hd" => $this->getRequest()->getBaseUrl().$option->getBackgroundImageUrl(),
                "tablet" => $this->getRequest()->getBaseUrl().$option->getBackgroundImageUrl()
            );
        } else if($option->getIsHomepage() OR $this->getApplication()->getUseHomepageBackgroundImageInSubpages()) {
            $urls = array(
                "standard" => $this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl(),
                "hd" => $this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("hd"),
                "tablet" => $this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("tablet")
            );
        }

        $this->_sendHtml($urls);

    }

    protected function _getBackgroundImage() {

        $url = "";
        $option = $this->getCurrentOptionValue();

        if($option->getIsHomepage()) {
            $url = $this->getApplication()->getBackgroundImageUrl("retina4");
        } else if($option->getHasBackgroundImage()) {
            $url = $option->getBackgroundImageUrl();
        } else if($option->getUseHomepageBackgroundImage()) {
            $url = $this->getApplication()->getHomepageBackgroundImageUrl("retina");
        }

        return $url;
    }

    private function __getAdmobSettings() {

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
                "id" => $device->getOwnerAdmobId(),
                "type" => $device->getOwnerAdmobType()
            );

        } else {
            if($pe_use_ads) {

                $settings = array(
                    "id" => System_Model_Config::getValueFor("application_".$device->getType()->getOsName()."_owner_admob_id"),
                    "type" => System_Model_Config::getValueFor("application_".$device->getType()->getOsName()."_owner_admob_type")
                );

            } else {

                if (System_Model_Config::getValueFor("application_owner_use_ads")) {

                    $settings = array(
                        "id" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_id"),
                        "type" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_type")
                    );

                } else {

                    $settings = array(
                        "id" => $device->getAdmobId(),
                        "type" => $device->getAdmobType()
                    );

                }
            }
        }

        return $settings;
    }

    /** Refresh the FB Token on login, and update the customer_social table. */
    private function __refreshFBToken($customer) {
        $customer_fb_datas = $customer->getSocialDatas("facebook");

        if(!empty($customer_fb_datas) && isset($customer_fb_datas["datas"])) {
            $social_datas = unserialize($customer_fb_datas["datas"]);
            if(isset($social_datas["access_token"])) {
                $access_token = Core_Model_Lib_Facebook::getOrRefreshToken($social_datas["access_token"]);

                $social_datas["access_token"] = $access_token;
                $customer_fb_datas["datas"] = $social_datas;
                $customer_fb_datas["id"] = $customer_fb_datas["social_id"];
                $customer->setSocialData('facebook', $customer_fb_datas);
                $customer->save();
            }
        }


    }

}
