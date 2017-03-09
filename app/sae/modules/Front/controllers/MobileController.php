<?php

/**
 * Class Front_MobileController
 *
 * @cache app_%ID%
 *
 */

class Front_MobileController extends Application_Controller_Mobile_Default {

    /**
     * Compiling all 3 main app request into one BIG
     *
     * Caching every 3 big blocks independently
     */
    public function loadv2Action() {
        /** Caching each block independently, to optimize loading */

        $application = $this->getApplication();
        $app_id = $application->getId();
        $request = $this->getRequest();
        $current_language = Core_Model_Language::getCurrentLanguage();

        /** ========== CSS Cache ========== */
        $cache_id_css = "front_mobile_load_css_app_{$app_id}";
        if(!$result = $this->cache->load($cache_id_css)) {

            $css_file = Core_Model_Directory::getBasePathTo(Template_Model_Design::getCssPath($application));

            $data_css = array(
                "css" => file_get_contents($css_file)
            );

            $this->cache->save($data_css, $cache_id_css, array(
                "front_mobile_load_css",
                "css_app_".$app_id
            ));

            $data_css["x-cache"] = "MISS";
        } else {
            $data_css = $result;

            $data_css["x-cache"] = "HIT";
        }
        /** ========== !CSS Cache ========== */

        /** ========== Load Cache ========== */
        $cache_id_loadv2 = "front_mobile_load_app_{$app_id}";
        if(!$result = $this->cache->load($cache_id_loadv2)) {

            # Compress homepage default
            $homepage_image = Core_Model_Directory::getBasePathTo($this->getApplication()->getHomepageBackgroundImageUrl());
            $homepage_image_b64 = Siberian_Image::open($homepage_image)->cropResize(256)->inline();

            $google_maps_key = $application->getGooglemapsKey();
            if(!empty($google_maps_key)) {
                $googlemaps_key = $application->getGooglemapsKey();
            } else {
                $api = Api_Model_Key::findKeysFor("googlemaps");
                $googlemaps_key = $api->getSecretKey();
            }

            $privacy_policy = trim($application->getPrivacyPolicy());
            if(empty($privacy_policy)) {
                $privacy_policy = false;
            }

            $data_load = array(
                "application" => array(
                    "id"            => $app_id,
                    "name"          => $application->getName(),
                    "is_locked"     => !!$application->requireToBeLoggedIn(),
                    "is_bo_locked"  => !!$application->getIsLocked(),
                    "colors" => array(
                        "header" => array(
                            "backgroundColor"   => $application->getBlock("header")->getBackgroundColorRGB(),
                            "color"             => $application->getBlock("header")->getColorRGB()
                        ),
                        "background" => array(
                            "backgroundColor"   => $application->getBlock("background")->getBackgroundColor(),
                            "color"             => $application->getBlock("background")->getColor()
                        ),
                        "list_item" => array(
                            "color" => $application->getBlock("list_item")->getColor()
                        )
                    ),
                    "admob" => $this->__getAdmobSettings(),
                    "facebook" => array(
                        "id"    => $application->getFacebookId(),
                        "scope" => Customer_Model_Customer_Type_Facebook::getScope()
                    ),
                    "gcm_senderid"                  => Push_Model_Certificate::getAndroidSenderId(),
                    "gcm_iconcolor"                 => $application->getAndroidPushColor(),
                    "googlemaps_key"                => $googlemaps_key,
                    "offline_content"               => !!$application->getOfflineContent(),
                    "ios_status_bar_is_hidden"      => !!$application->getIosStatusBarIsHidden(),
                    "android_status_bar_is_hidden"  => !!$application->getAndroidStatusBarIsHidden(),
                    "privacy_policy"                => str_replace("#APP_NAME", $application->getName(), $privacy_policy),
                ),
                "homepage_image" => $homepage_image_b64
            );
            $this->cache->save($data_load, $cache_id_loadv2, array(
                "front_mobile_load",
                "app_".$app_id
            ));

            $data_load["x-cache"] = "MISS";
        } else {
            $data_load = $result;

            $data_load["x-cache"] = "HIT";
        }
        /** ========== !Load Cache ========== */



        /** ========== Homepage, Layout, Features ========== */
        $cache_id_homepage = "front_mobile_home_findall_app_{$application->getId()}_locale_{$current_language}";
        if(!$result = $this->cache->load($cache_id_homepage)) {

            $option_values = $application->getPages(10);
            $data_pages = array();
            $color = $application->getBlock("tabbar")->getImageColor();
            $background_color = $application->getBlock("tabbar")->getBackgroundColor();

            foreach ($option_values as $option_value) {
                try {
                    $data_pages[] = array(
                        "value_id"          => $option_value->getId(),
                        "id"                => intval($option_value->getId()),
                        "layout_id"         => $option_value->getLayoutId(),
                        "code"              => $option_value->getCode(),
                        "name"              => $option_value->getTabbarName(),
                        "subtitle"          => $option_value->getTabbarSubtitle(),
                        "is_active"         => !!$option_value->isActive(),
                        "url"               => $option_value->getUrl(null, array("value_id" => $option_value->getId()), false),
                        "path"              => $option_value->getPath(null, array("value_id" => $option_value->getId()), false),
                        "icon_url"          => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option_value->getIconId(), $color),
                        "icon_is_colorable" => !!$option_value->getImage()->getCanBeColorized(),
                        "is_locked"         => !!$option_value->isLocked(),
                        "is_link"           => !$option_value->getIsAjax(),
                        "use_my_account"    => $option_value->getUseMyAccount(),
                        "use_nickname"      => $option_value->getUseNickname(),
                        "use_ranking"       => $option_value->getUseRanking(),
                        "offline_mode"      => !!$option_value->getObject()->isCacheable(),
                        "custom_fields"     => $option_value->getCustomFields(),
                        "position"          => $option_value->getPosition()
                    );
                } catch (Exception $e) {
                    # Silently fail missing modules
                    log_alert("A module is possibly missing, ".$e->getMessage());
                }
            }

            $option = new Application_Model_Option();
            $option->findTabbarMore();

            $more_colorizable = true;
            if ($application->getMoreIconId()) {
                $library = new Media_Model_Library_Image();
                $icon = $library->find($application->getMoreIconId());
                if (!$icon->getCanBeColorized()) {
                    $more_color = null;
                } else {
                    $more_color = $color;
                }

                $more_colorizable = $icon->getCanBeColorized();
            } else {
                $more_color = $color;
            }

            $data_more_items = array(
                "code"                  => $option->getCode(),
                "name"                  => $option->getTabbarName(),
                "subtitle"              => $application->getMoreSubtitle(),
                "is_active"             => !!$option->isActive(),
                "url"                   => "",
                "icon_url"              => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $more_color),
                "icon_is_colorable"     => !!$more_colorizable,
            );

            $option = new Application_Model_Option();
            $option->findTabbarAccount();

            $account_colorizable = true;
            if ($application->getAccountIconId()) {
                $library = new Media_Model_Library_Image();
                $icon = $library->find($application->getAccountIconId());
                if (!$icon->getCanBeColorized()) {
                    $account_color = null;
                } else {
                    $account_color = $color;
                }

                $account_colorizable = $icon->getCanBeColorized();
            } else {
                $account_color = $color;
            }

            $data_customer_account = array(
                "code"                  => $option->getCode(),
                "name"                  => $option->getTabbarName(),
                "subtitle"              => $application->getAccountSubtitle(),
                "is_active"             => !!$option->isActive(),
                "url"                   => $this->getUrl("customer/mobile_account_login"),
                "path"                  => $this->getPath("customer/mobile_account_login"),
                "login_url"             => $this->getUrl("customer/mobile_account_login"),
                "login_path"            => $this->getPath("customer/mobile_account_login"),
                "edit_url"              => $this->getUrl("customer/mobile_account_edit"),
                "edit_path"             => $this->getPath("customer/mobile_account_edit"),
                "icon_url"              => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $account_color),
                "icon_is_colorable"     => !!$account_colorizable,
                "is_visible"            => !!$application->usesUserAccount()
            );

            $layout = new Application_Model_Layout_Homepage();
            $layout->find($application->getLayoutId());

            $layout_options = $application->getLayoutOptions();
            if (!empty($layout_options) && $opts = Siberian_Json::decode($layout_options)) {
                $layout_options = $opts;
            } else {
                $layout_options = false;
            }

            # Homepage slider
            $homepage_slider_images = array();
            $slider_images = $application->getSliderImages();
            foreach ($slider_images as $slider_image) {
                $homepage_slider_images[] = $slider_image->getLink();
            }

            $data_homepage = array(
                "pages"                         => $data_pages,
                "more_items"                    => $data_more_items,
                "customer_account"              => $data_customer_account,
                "layout" => array(
                    "layout_id"                 => "l{$application->getLayoutId()}",
                    "layout_code"               => $application->getLayout()->getCode(),
                    "layout_options"            => $layout_options,
                    "visibility"                => $application->getLayoutVisibility(),
                    "use_horizontal_scroll"     => !!$layout->getUseHorizontalScroll(),
                    "position"                  => $layout->getPosition()
                ),
                "limit_to"                              => $application->getLayout()->getNumberOfDisplayedIcons(),
                "layout_id"                             => "l{$application->getLayoutId()}",
                "layout_code"                           => $application->getLayout()->getCode(),
                "tabbar_is_transparent"                 => !!($background_color == "transparent"),
                "homepage_slider_is_visible"            => !!$application->getHomepageSliderIsVisible(),
                "homepage_slider_duration"              => $application->getHomepageSliderDuration(),
                "homepage_slider_loop_at_beginning"     => !!$application->getHomepageSliderLoopAtBeginning(),
                "homepage_slider_size"                  => $application->getHomepageSliderSize(),
                "homepage_slider_is_new"                => !!($application->getHomepageSliderSize() != null),
                "homepage_slider_images"                => $homepage_slider_images,
            );

            $this->cache->save($data_homepage, $cache_id_homepage, array(
                "front_mobile_home_findall",
                "app_".$application->getId(),
                "homepage_app_".$application->getId(),
                "css_app_".$app_id,
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ));

            $data_homepage["x-cache"] = "MISS";
        } else {

            $data_homepage = $result;

            $data_homepage["x-cache"] = "HIT";
        }

        /** Don't cache customer */
        $push_number = 0;
        if ($device_uid = $this->getRequest()->getParam("device_uid")) {
            $message = new Push_Model_Message();
            $push_number = $message->countByDeviceId($device_uid);
        }
        $data_homepage["push_badge"] = $push_number;
        /** ========== !Homepage, Layout, Features ========== */


        /** ========== Translations ========== */
        # Cache is based on locale/app_id.
        $cache_id_translation = "application_mobile_translation_findall_app_{$app_id}_locale_{$current_language}";
        if(!$result = $this->cache->load($cache_id_translation)) {

            Siberian_Cache_Translation::init();

            $data_translation = Core_Model_Translator::getTranslationsFor($application->getDesignCode());

            if(empty($data_translation)) {
                $data_translation = array("_empty-translation-cache_" => true);
            }

            $this->cache->save($data_translation, $cache_id_translation, array(
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ));

            $data_translation["x-cache"] = "MISS";
        } else {

            $data_translation = $result;

            $data_translation["x-cache"] = "HIT";
        }
        $data_translation["_locale"] = strtolower(str_replace("_", "-", $current_language));
        /** ========== !Translations ========== */


        /** Don't cache customer */
        $session = $this->getSession();
        $customer_id = $this->getSession()->getCustomerId();
        $this->__refreshFBToken($this->getSession()->getCustomer());

        $data_load["customer"] = array(
            "id"                            => $customer_id,
            "can_connect_with_facebook"     => !!$application->getFacebookId(),
            "can_access_locked_features"    => !!($customer_id && $session->getCustomer()->canAccessLockedFeatures()),
            "token"                         => Zend_Session::getId()
        );

        $data = array(
            "load" => $data_load,
            "css" => $data_css,
            "homepage" => $data_homepage,
            "translation" => $data_translation,
        );

        /** Force no cache */
        $response = $this->getResponse();
        $response->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $response->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $response->setHeader("Pragma", "no-cache");

        $this->_sendJson($data);
    }

    /**
     * @deprecated
     *
     * Caching load action.
     */
    public function loadAction() {
        $application = $this->getApplication();

        $cache_id = "pre4812_front_mobile_load_app_{$application->getId()}";

        if(!$result = $this->cache->load($cache_id)) {

            # Compress homepage default
            $homepage_image = Core_Model_Directory::getBasePathTo($this->getApplication()->getHomepageBackgroundImageUrl());
            $homepage_image_b64 = Siberian_Image::open($homepage_image)->cropResize(256)->inline();

            $google_maps_key = $application->getGooglemapsKey();
            if(!empty($google_maps_key)) {
                $googlemaps_key = $application->getGooglemapsKey();
            } else {
                $api = Api_Model_Key::findKeysFor("googlemaps");
                $googlemaps_key = $api->getSecretKey();
            }

            $privacy_policy = trim($application->getPrivacyPolicy());
            if(empty($privacy_policy)) {
                $privacy_policy = false;
            }

            $data = array(
                "css" => $this->getRequest()->getBaseUrl().Template_Model_Design::getCssPath($application),
                "application" => array(
                    "id"            => $application->getId(),
                    "name"          => $application->getName(),
                    "is_locked"     => $application->requireToBeLoggedIn(),
                    "is_bo_locked"  => $application->getIsLocked(),
                    "colors" => array(
                        "header" => array(
                            "backgroundColor"   => $application->getBlock("header")->getBackgroundColorRGB(),
                            "color"             => $application->getBlock("header")->getColorRGB()
                        ),
                        "background" => array(
                            "backgroundColor"   => $application->getBlock("background")->getBackgroundColor(),
                            "color"             => $application->getBlock("background")->getColor()
                        ),
                        "list_item" => array(
                            "color" => $application->getBlock("list_item")->getColor()
                        )
                    ),
                    "admob" => $this->__getAdmobSettings(),
                    "facebook" => array(
                        "id"    => $application->getFacebookId(),
                        "scope" => Customer_Model_Customer_Type_Facebook::getScope()
                    ),
                    "gcm_senderid"                  => Push_Model_Certificate::getAndroidSenderId(),
                    "gcm_iconcolor"                 => $application->getAndroidPushColor(),
                    "googlemaps_key"                => $googlemaps_key,
                    "offline_content"               => ($application->getOfflineContent() == 1),
                    "ios_status_bar_is_hidden"      => ($application->getIosStatusBarIsHidden() == 1),
                    "android_status_bar_is_hidden"  => ($application->getAndroidStatusBarIsHidden() == 1),
                    "privacy_policy"                => str_replace("#APP_NAME", $application->getName(), $privacy_policy),
                ),
                "homepage_image" => $homepage_image_b64
            );

            $this->cache->save($data, $cache_id, array(
                "front_mobile_load",
                "app_".$application->getId()
            ));

            $data["x-cache"] = "MISS";
        } else {
            $data = $result;

            $data["x-cache"] = "HIT";
        }

        /** Don't cache customer */
        $customer_id = $this->getSession()->getCustomerId();
        $this->__refreshFBToken($this->getSession()->getCustomer());

        $data["customer"] = array(
            "id"                            => $customer_id,
            "can_connect_with_facebook"     => !!$application->getFacebookId(),
            "can_access_locked_features"    => $customer_id && $this->getSession()->getCustomer()->canAccessLockedFeatures(),
            "token"                         => Zend_Session::getId()
        );

        $this->_sendJson($data);
    }

    public function styleAction() {
        $html = $this->getLayout()->addPartial('style', 'core_view_mobile_default', 'page/css.phtml')->toHtml();
        $this->getLayout()->setHtml($html);
    }

    public function backgroundimageAction() {

        $urls = array(
            "standard" => "",
            "hd" => "",
            "tablet" => ""
        );

        $option = $this->getCurrentOptionValue();

        if($this->getRequest()->getParam("value_id") == "home") {
            $urls = array(
                "standard" => $this->clean_url($this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl()),
                "hd" => $this->clean_url($this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("hd")),
                "tablet" => $this->clean_url($this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("tablet"))
            );
        } else if($option->hasBackgroundImage() AND $option->getBackgroundImage() != "no-image") {
            $urls = array(
                "standard" => $this->clean_url($this->getRequest()->getBaseUrl().$option->getBackgroundImageUrl()),
                "hd" => $this->clean_url($this->getRequest()->getBaseUrl().$option->getBackgroundImageUrl()),
                "tablet" => $this->clean_url($this->getRequest()->getBaseUrl().$option->getBackgroundImageUrl())
            );
        } else if($option->getIsHomepage() OR $this->getApplication()->getUseHomepageBackgroundImageInSubpages()) {
            $urls = array(
                "standard" => $this->clean_url($this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl()),
                "hd" => $this->clean_url($this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("hd")),
                "tablet" => $this->clean_url($this->getRequest()->getBaseUrl().$this->getApplication()->getHomepageBackgroundImageUrl("tablet"))
            );
        }

        $this->_sendJson($urls);

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
