<?php

use Siberian\File;

/**
 * Class Front_MobileController
 *
 * @cache app_%ID%
 *
 */

class Front_MobileController extends Application_Controller_Mobile_Default
{

    /**
     * Compiling all 3 main app request into one BIG
     *
     * Caching every 3 big blocks independently
     */
    public function loadv3Action()
    {

        /** Caching each block independently, to optimize loading */

        $application = $this->getApplication();
        $app_id = $application->getId();
        $request = $this->getRequest();
        $current_language = Core_Model_Language::getCurrentLanguage();

        /** ========== CSS Cache ========== */
        $cache_id_css = "v3_front_mobile_load_css_app_{$app_id}";
        if (!$result = $this->cache->load($cache_id_css)) {

            $css_file = Core_Model_Directory::getBasePathTo(Template_Model_Design::getCssPath($application));

            $data_css = [
                "css" => file_get_contents($css_file)
            ];

            $this->cache->save($data_css, $cache_id_css, [
                "v3",
                "front_mobile_load_css",
                "css_app_" . $app_id
            ]);

            $data_css["x-cache"] = "MISS";
        } else {
            $data_css = $result;

            $data_css["x-cache"] = "HIT";
        }
        /** ========== !CSS Cache ========== */

        /** ========== Load Cache ========== */
        $cache_id_loadv2 = "v3_front_mobile_load_app_{$app_id}";
        if (!$result = $this->cache->load($cache_id_loadv2)) {

            # Compress homepage default
            $homepage_image = Core_Model_Directory::getBasePathTo($this->getApplication()->getHomepageBackgroundImageUrl());
            $homepage_image_b64 = Siberian_Image::open($homepage_image)->cropResize(256)->inline();

            $googlemaps_key = $application->getGooglemapsKey();

            $privacy_policy = trim($application->getPrivacyPolicy());
            if (empty($privacy_policy)) {
                $privacy_policy = false;
            }

            $privacy_policy_title = trim($application->getPrivacyPolicyTitle());
            if (empty($privacy_policy_title)) {
                $privacy_policy_title = __("Privacy policy");
            }

            $icon_color = strtolower($application->getAndroidPushColor());
            if (!preg_match("/^#[a-f0-9]{6}$/", $icon_color)) {
                # Fallback with a number only color ...
                $icon_color = "#808080";
            }

            $progressbar_color = $application->getBlock("dialog_text")->getColor();
            $progressbar_trail_color = $application->getBlock("dialog_bg")->getColor();
            $progressbar_color = Siberian_Color::newColor($progressbar_color, "hex");
            $progressbar_trail_color = Siberian_Color::newColor($progressbar_trail_color, "hex");

            if ($progressbar_trail_color->lightness > 80) {
                $progressbar_trail_color = $progressbar_trail_color->getNew("lightness", $progressbar_color->lightness - 20);
            } else {
                $progressbar_trail_color = $progressbar_trail_color->getNew("lightness", $progressbar_color->lightness + 20);
            }

            $bg_block = $application->getBlock("background");
            $bg_color_hex = $bg_block->getBackgroundColor();
            $bg_color = Siberian_Color::newColor($bg_color_hex, "hex");
            $bg_color->alpha = $bg_block->getBackgroundOpacity() / 100;

            $colorStatusBar = Siberian_Color::newColor($application->getBlock("header")->getBackgroundColor(), 'hex');
            $colorStatusBarLighten = $colorStatusBar->getNew('lightness', $colorStatusBar->lightness - 10);

            $data_load = [
                "application" => [
                    "id" => $app_id,
                    "name" => $application->getName(),
                    "is_locked" => (boolean)$application->requireToBeLoggedIn(),
                    "is_bo_locked" => (boolean)$application->getIsLocked(),
                    "colors" => [
                        "header" => [
                            "statusBarColor" => $colorStatusBarLighten->toCSS("hex"),
                            "backgroundColor" => $application->getBlock("header")->getBackgroundColorRGB(),
                            "color" => $application->getBlock("header")->getColorRGB()
                        ],
                        "background" => [
                            "backgroundColor" => $bg_color_hex,
                            "color" => $application->getBlock("background")->getColor(),
                            "rgba" => $bg_color->toCSS('rgba')
                        ],
                        "loader" => [
                            "trail" => $progressbar_trail_color->toCSS("hex"),
                            "bar_text" => $progressbar_color->toCSS("hex"),
                        ],
                        "list_item" => [
                            "color" => $application->getBlock("list_item")->getColor()
                        ]
                    ],
                    "admob" => $this->__getAdmobSettings(),
                    "admob_v2" => $this->__getAdmobSettingsV2(),
                    "facebook" => [
                        "id" => empty($application->getFacebookId()) ? null : $application->getFacebookId(),
                        "scope" => Customer_Model_Customer_Type_Facebook::getScope()
                    ],
                    //"gcm_senderid" => Push_Model_Certificate::getAndroidSenderId(),
                    "gcm_iconcolor" => $icon_color,
                    "googlemaps_key" => $googlemaps_key,
                    "offline_content" => (boolean)$application->getOfflineContent(),
                    "ios_status_bar_is_hidden" => (boolean)$application->getIosStatusBarIsHidden(),
                    "android_status_bar_is_hidden" => (boolean)$application->getAndroidStatusBarIsHidden(),
                    "privacy_policy_title" => $privacy_policy_title,
                    "privacy_policy" => str_replace("#APP_NAME", $application->getName(), $privacy_policy),
                    'privacy_policy_gdpr' => $application->getPrivacyPolicyGdpr(),
                    'gdprIsEnabled' => isGdpr(),
                    "homepage_background" => (boolean)$application->getUseHomepageBackgroundImageInSubpages(),
                    'backButton' => (string)$application->getBackButton(),
                ],
                "homepage_image" => $homepage_image_b64
            ];
            $this->cache->save($data_load, $cache_id_loadv2, [
                "v3",
                "front_mobile_load",
                "app_" . $app_id
            ]);

            $data_load["x-cache"] = "MISS";
        } else {
            $data_load = $result;

            $data_load["x-cache"] = "HIT";
        }
        /** ========== !Load Cache ========== */


        /** ========== Homepage, Layout, Features ========== */
        $cache_id_homepage = "v3_front_mobile_home_findall_app_{$application->getId()}_locale_{$current_language}";
        if (!$result = $this->cache->load($cache_id_homepage)) {

            $option_values = $application->getPages(10, true);
            $data_pages = [];
            $color = $application->getBlock("tabbar")->getImageColor();
            $background_color = $application->getBlock("tabbar")->getBackgroundColor();

            $touched_values = [];
            foreach ($option_values as $option_value) {

                $touched_values[$option_value->getId()] = [
                    "touched_at" => (integer)$option_value->getTouchedAt(),
                    "expires_at" => (integer)$option_value->getExpiresAt()
                ];

                try {
                    $object = $option_value->getObject();

                    /**
                     * In-App-Browser / Browser options.
                     */
                    $hide_navbar = null;
                    $use_external_app = null;
                    if ($object->getLink() &&
                        $option_value->getCode() === "weblink_mono") {
                        $hide_navbar = $object->getLink()->getHideNavbar();
                        $use_external_app = $object->getLink()->getUseExternalApp();
                    }

                    if (sizeof($option_values) >= 50) {
                        if ($option_value->getCode() === "folder") {
                            $embed_payload = false;
                        } else {
                            $embed_payload = $option_value->getEmbedPayload($request);
                        }
                    } else {
                        $embed_payload = $option_value->getEmbedPayload($request);
                    }

                    /**
                     * END Link special code
                     */

                    $_tmpData = [
                        "value_id" => (integer)$option_value->getId(),
                        "id" => (integer)$option_value->getId(),
                        "layout_id" => (integer)$option_value->getLayoutId(),
                        "code" => $option_value->getCode(),
                        "name" => $option_value->getTabbarName(),
                        "subtitle" => $option_value->getTabbarSubtitle(),
                        "is_active" => (boolean)$option_value->isActive(),
                        "url" => $option_value->getUrl(null, ["value_id" => $option_value->getId()], false),
                        "hide_navbar" => (boolean)$hide_navbar,
                        "use_external_app" => (boolean)$use_external_app,
                        "path" => $option_value->getPath(null, ["value_id" => $option_value->getId()], 'mobile'),
                        "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option_value->getIconId(), $color),
                        "icon_is_colorable" => (boolean)$option_value->getImage()->getCanBeColorized(),
                        "is_locked" => (boolean)$option_value->isLocked(),
                        "is_link" => (boolean)!$option_value->getIsAjax(),
                        "use_my_account" => (boolean)$option_value->getUseMyAccount(),
                        "use_nickname" => (boolean)$option_value->getUseNickname(),
                        "use_birthdate" => (boolean)$option_value->getUseBirthdate(),
                        "use_ranking" => (boolean)$option_value->getUseRanking(),
                        "use_civility" => (boolean)$option_value->getUseCivility(),
                        "use_mobile" => (boolean)$option_value->getUseMobile(),
                        "offline_mode" => (boolean)$option_value->getObject()->isCacheable(),
                        "custom_fields" => $option_value->getCustomFields(),
                        "embed_payload" => $embed_payload,
                        "position" => (integer)$option_value->getPosition(),
                        "homepage" => (boolean)($option_value->getFolderCategoryId() === null),
                        "touched_at" => (integer)$option_value->getTouchedAt(),
                        "expires_at" => (integer)$option_value->getExpiresAt()
                    ];

                    // Hotfix pre 4.16 for m-commerce.
                    if ($_tmpData["code"] === "m_commerce") {
                        $_tmpData["path"] = sprintf("/%s/mcommerce/mobile_category/index/value_id/%s",
                            $application->getKey(),
                            $_tmpData["value_id"]);
                    }

                    $data_pages[] = $_tmpData;
                } catch (Exception $e) {
                    # Silently fail missing modules
                    log_alert("A module is possibly missing, " . $e->getMessage());
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

            $data_more_items = [
                "code" => $option->getCode(),
                "name" => $option->getTabbarName(),
                "subtitle" => $application->getMoreSubtitle(),
                "is_active" => (boolean)$option->isActive(),
                "url" => "",
                "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $more_color),
                "icon_is_colorable" => (boolean)$more_colorizable,
            ];

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

            $data_customer_account = [
                "code" => $option->getCode(),
                "name" => $option->getTabbarName(),
                "subtitle" => $application->getAccountSubtitle(),
                "is_active" => (boolean)$option->isActive(),
                "url" => $this->getUrl("customer/mobile_account_login"),
                "path" => $this->getPath("customer/mobile_account_login"),
                "login_url" => $this->getUrl("customer/mobile_account_login"),
                "login_path" => $this->getPath("customer/mobile_account_login"),
                "edit_url" => $this->getUrl("customer/mobile_account_edit"),
                "edit_path" => $this->getPath("customer/mobile_account_edit"),
                "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $account_color),
                "icon_is_colorable" => (boolean)$account_colorizable,
                "is_visible" => (boolean)$application->usesUserAccount()
            ];

            $layout = new Application_Model_Layout_Homepage();
            $layout->find($application->getLayoutId());

            $layout_options = $application->getLayoutOptions();
            if (!empty($layout_options) && $opts = Siberian_Json::decode($layout_options)) {
                $layout_options = $opts;
            } else {
                $layout_options = false;
            }

            # Homepage slider
            $homepage_slider_images = [];
            $slider_images = $application->getSliderImages();
            foreach ($slider_images as $slider_image) {
                $homepage_slider_images[] = $slider_image->getLink();
            }

            $data_homepage = [
                "pages" => $data_pages,
                "touched" => $touched_values,
                "more_items" => $data_more_items,
                "customer_account" => $data_customer_account,
                "layout" => [
                    "layout_id" => "l{$application->getLayoutId()}",
                    "layout_code" => $application->getLayout()->getCode(),
                    "layout_options" => $layout_options,
                    "visibility" => $application->getLayoutVisibility(),
                    "use_horizontal_scroll" => (boolean)$layout->getUseHorizontalScroll(),
                    "position" => $layout->getPosition()
                ],
                "limit_to" => $application->getLayout()->getNumberOfDisplayedIcons() * 1,
                "layout_id" => "l{$application->getLayoutId()}",
                "layout_code" => $application->getLayout()->getCode(),
                "tabbar_is_transparent" => (boolean)($background_color == "transparent"),
                "homepage_slider_is_visible" => (boolean)$application->getHomepageSliderIsVisible(),
                "homepage_slider_duration" => $application->getHomepageSliderDuration(),
                "homepage_slider_loop_at_beginning" => (boolean)$application->getHomepageSliderLoopAtBeginning(),
                "homepage_slider_size" => $application->getHomepageSliderSize(),
                "homepage_slider_opacity" => (integer)$application->getHomepageSliderOpacity(),
                "homepage_slider_offset" => (integer)$application->getHomepageSliderOffset(),
                "homepage_slider_is_new" => (boolean)($application->getHomepageSliderSize() != null),
                "homepage_slider_images" => $homepage_slider_images,
            ];

            foreach ($application->getOptions() as $opt) {
                $data_homepage['layouts'][$opt->getValueId()] = $opt->getLayoutId();
            }

            $this->cache->save($data_homepage, $cache_id_homepage, [
                "v3",
                "front_mobile_home_findall",
                "app_" . $application->getId(),
                "homepage_app_" . $application->getId(),
                "css_app_" . $app_id,
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ]);

            $data_homepage["x-cache"] = "MISS";
        } else {

            $data_homepage = $result;

            $data_homepage["x-cache"] = "HIT";
        }

        /** Don't cache customer */
        $push_number = 0;
        //if ($device_uid = $this->getRequest()->getParam("device_uid")) {
        //    $message = new Push_Model_Message();
        //    $push_number = $message->countByDeviceId($device_uid);
        //}
        $data_homepage["push_badge"] = $push_number;
        /** ========== !Homepage, Layout, Features ========== */


        /** ========== Translations ========== */
        # Cache is based on locale/app_id.
        $cache_id_translation = "v3_application_mobile_translation_findall_app_{$app_id}_locale_{$current_language}";
        if (!$result = $this->cache->load($cache_id_translation)) {

            Siberian_Cache_Translation::init();

            $data_translation = Core_Model_Translator::getTranslationsFor($application->getDesignCode());

            if (empty($data_translation)) {
                $data_translation = ["_empty-translation-cache_" => true];
            }

            $this->cache->save($data_translation, $cache_id_translation, [
                "v3",
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ]);

            $data_translation["x-cache"] = "MISS";
        } else {

            $data_translation = $result;

            $data_translation["x-cache"] = "HIT";
        }
        $data_translation["_locale"] = strtolower(str_replace("_", "-", $current_language));
        /** ========== !Translations ========== */


        /** Don't cache customer */
        $session = $this->getSession();
        $customer = $session->getCustomer();
        $customer_id = $customer->getCustomerId();
        $this->__refreshFBToken($customer);

        $is_logged_in = false;

        $data_load["customer"] = [
            "id" => (integer)$customer_id,
            "can_connect_with_facebook" => (boolean)$application->getFacebookId(),
            "can_access_locked_features" => (boolean)($customer_id && $session->getCustomer()->canAccessLockedFeatures()),
            "token" => Zend_Session::getId()
        ];

        if ($customer_id) {
            $metadata = $session->getCustomer()->getMetadatas();
            if (empty($metadata)) {
                $metadata = json_decode("{}"); // we really need a javascript object here
            }

            //hide stripe customer id for secure purpose
            if ($metadata->stripe && array_key_exists("customerId", $metadata->stripe) && $metadata->stripe["customerId"]) {
                unset($metadata->stripe["customerId"]);
            }

            $is_logged_in = true;

            $data_load["customer"] = array_merge($data_load["customer"], [
                "civility" => $customer->getCivility(),
                "firstname" => $customer->getFirstname(),
                "lastname" => $customer->getLastname(),
                "nickname" => $customer->getNickname(),
                "email" => $customer->getEmail(),
                "show_in_social_gaming" => (boolean)$customer->getShowInSocialGaming(),
                "is_custom_image" => (boolean)$customer->getIsCustomImage(),
                "metadatas" => $metadata,
                "can_connect_with_facebook" => (boolean)$application->getFacebookId(),
                "can_access_locked_features" => (boolean)($customer_id && $session->getCustomer()->canAccessLockedFeatures()),
            ]);

            if (Siberian_CustomerInformation::isRegistered("stripe")) {
                $exporter_class = Siberian_CustomerInformation::getClass("stripe");
                if (class_exists($exporter_class) && method_exists($exporter_class, "getInformation")) {
                    $tmp_class = new $exporter_class();
                    $info = $tmp_class->getInformation($customer->getId());
                    $data["stripe"] = $info ? $info : [];
                }
            }
        }

        $data_load["customer"] = array_merge($data_load["customer"], [
            "is_logged_in" => $is_logged_in,
            "isLoggedIn" => $is_logged_in
        ]);

        /** Get the most recent cache update */
        $updated_at = max([
            $this->cache->test($cache_id_css),
            $this->cache->test($cache_id_loadv2),
            $this->cache->test($cache_id_homepage),
            $this->cache->test($cache_id_translation),
        ]);

        /** Web App manifest */
        $data_manifest = $this->generatewebappconfig();

        $data = [
            "load" => $data_load,
            "css" => $data_css,
            "homepage" => $data_homepage,
            "manifest" => $data_manifest,
            "translation" => $data_translation,
        ];

        /** Force no cache */
        $response = $this->getResponse();
        $response->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $response->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $response->setHeader("Pragma", "no-cache");
        $response->setHeader("X-Cache-Last-Update", $updated_at);

        $this->_sendJson($data);
    }


    /**
     * Compiling all 3 main app request into one BIG
     *
     * Caching every 3 big blocks independently
     */
    public function loadv2Action()
    {
        /** Caching each block independently, to optimize loading */

        $application = $this->getApplication();
        $app_id = $application->getId();
        $request = $this->getRequest();
        $current_language = Core_Model_Language::getCurrentLanguage();

        /** ========== CSS Cache ========== */
        $cache_id_css = "front_mobile_load_css_app_{$app_id}";
        if (!$result = $this->cache->load($cache_id_css)) {

            $css_file = Core_Model_Directory::getBasePathTo(Template_Model_Design::getCssPath($application));

            $data_css = [
                "css" => file_get_contents($css_file)
            ];

            $this->cache->save($data_css, $cache_id_css, [
                "front_mobile_load_css",
                "css_app_" . $app_id
            ]);

            $data_css["x-cache"] = "MISS";
        } else {
            $data_css = $result;

            $data_css["x-cache"] = "HIT";
        }
        /** ========== !CSS Cache ========== */

        /** ========== Load Cache ========== */
        $cache_id_loadv2 = "front_mobile_load_app_{$app_id}";
        if (!$result = $this->cache->load($cache_id_loadv2)) {

            # Compress homepage default
            $homepage_image = Core_Model_Directory::getBasePathTo($this->getApplication()->getHomepageBackgroundImageUrl());
            $homepage_image_b64 = Siberian_Image::open($homepage_image)->cropResize(256)->inline();

            $googlemaps_key = $application->getGooglemapsKey();

            $privacy_policy = trim($application->getPrivacyPolicy());
            if (empty($privacy_policy)) {
                $privacy_policy = false;
            }

            $icon_color = strtolower($application->getAndroidPushColor());
            if (!preg_match("/^#[a-f0-9]{6}$/", $icon_color)) {

                # Fallback with a number only color ...
                $icon_color = "#808080";

            }

            $data_load = [
                "application" => [
                    "id" => $app_id,
                    "name" => $application->getName(),
                    "is_locked" => !!$application->requireToBeLoggedIn(),
                    "is_bo_locked" => !!$application->getIsLocked(),
                    "colors" => [
                        "header" => [
                            "backgroundColor" => $application->getBlock("header")->getBackgroundColorRGB(),
                            "color" => $application->getBlock("header")->getColorRGB()
                        ],
                        "background" => [
                            "backgroundColor" => $application->getBlock("background")->getBackgroundColor(),
                            "color" => $application->getBlock("background")->getColor()
                        ],
                        "list_item" => [
                            "color" => $application->getBlock("list_item")->getColor()
                        ]
                    ],
                    "admob" => $this->__getAdmobSettings(),
                    "admob_v2" => $this->__getAdmobSettingsV2(),
                    "facebook" => [
                        "id" => $application->getFacebookId(),
                        "scope" => Customer_Model_Customer_Type_Facebook::getScope()
                    ],
                    //"gcm_senderid" => Push_Model_Certificate::getAndroidSenderId(),
                    "gcm_iconcolor" => $icon_color,
                    "googlemaps_key" => $googlemaps_key,
                    "offline_content" => !!$application->getOfflineContent(),
                    "ios_status_bar_is_hidden" => !!$application->getIosStatusBarIsHidden(),
                    "android_status_bar_is_hidden" => !!$application->getAndroidStatusBarIsHidden(),
                    "privacy_policy" => str_replace("#APP_NAME", $application->getName(), $privacy_policy),
                ],
                "homepage_image" => $homepage_image_b64
            ];
            $this->cache->save($data_load, $cache_id_loadv2, [
                "front_mobile_load",
                "app_" . $app_id
            ]);

            $data_load["x-cache"] = "MISS";
        } else {
            $data_load = $result;

            $data_load["x-cache"] = "HIT";
        }
        /** ========== !Load Cache ========== */


        /** ========== Homepage, Layout, Features ========== */
        $cache_id_homepage = "front_mobile_home_findall_app_{$application->getId()}_locale_{$current_language}";
        if (!$result = $this->cache->load($cache_id_homepage)) {

            $option_values = $application->getPages(10);
            $data_pages = [];
            $color = $application->getBlock("tabbar")->getImageColor();
            $background_color = $application->getBlock("tabbar")->getBackgroundColor();

            foreach ($option_values as $option_value) {
                try {
                    $object = $option_value->getObject();
                    /**
                     * START Link special code
                     * We get informations about link at homepage level
                     */
                    $hide_navbar = null;
                    $use_external_app = null;
                    if ($object->getLink() &&
                        $option_value->getCode() === "weblink_mono") {
                        $hide_navbar = $object->getLink()->getHideNavbar();
                        $use_external_app = $object->getLink()->getUseExternalApp();
                    }
                    /**
                     * END Link special code
                     */
                    $data_pages[] = [
                        "value_id" => $option_value->getId(),
                        "id" => intval($option_value->getId()),
                        "layout_id" => $option_value->getLayoutId(),
                        "code" => $option_value->getCode(),
                        "name" => $option_value->getTabbarName(),
                        "subtitle" => $option_value->getTabbarSubtitle(),
                        "is_active" => !!$option_value->isActive(),
                        "url" => $option_value->getUrl(null, ["value_id" => $option_value->getId()], false),
                        "hide_navbar" => $hide_navbar,
                        "use_external_app" => $use_external_app,
                        "path" => $option_value->getPath(null, ["value_id" => $option_value->getId()], 'mobile'),
                        "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option_value->getIconId(), $color),
                        "icon_is_colorable" => !!$option_value->getImage()->getCanBeColorized(),
                        "is_locked" => !!$option_value->isLocked(),
                        "is_link" => !$option_value->getIsAjax(),
                        "use_my_account" => $option_value->getUseMyAccount(),
                        "use_birthdate" => $option_value->getUseBirthdate(),
                        "use_ranking" => $option_value->getUseRanking(),
                        "offline_mode" => !!$option_value->getObject()->isCacheable(),
                        "custom_fields" => $option_value->getCustomFields(),
                        "position" => $option_value->getPosition()
                    ];
                } catch (Exception $e) {
                    # Silently fail missing modules
                    log_alert("A module is possibly missing, " . $e->getMessage());
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

            $data_more_items = [
                "code" => $option->getCode(),
                "name" => $option->getTabbarName(),
                "subtitle" => $application->getMoreSubtitle(),
                "is_active" => !!$option->isActive(),
                "url" => "",
                "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $more_color),
                "icon_is_colorable" => !!$more_colorizable,
            ];

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

            $data_customer_account = [
                "code" => $option->getCode(),
                "name" => $option->getTabbarName(),
                "subtitle" => $application->getAccountSubtitle(),
                "is_active" => !!$option->isActive(),
                "url" => $this->getUrl("customer/mobile_account_login"),
                "path" => $this->getPath("customer/mobile_account_login"),
                "login_url" => $this->getUrl("customer/mobile_account_login"),
                "login_path" => $this->getPath("customer/mobile_account_login"),
                "edit_url" => $this->getUrl("customer/mobile_account_edit"),
                "edit_path" => $this->getPath("customer/mobile_account_edit"),
                "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $account_color),
                "icon_is_colorable" => !!$account_colorizable,
                "is_visible" => !!$application->usesUserAccount()
            ];

            $layout = new Application_Model_Layout_Homepage();
            $layout->find($application->getLayoutId());

            $layout_options = $application->getLayoutOptions();
            if (!empty($layout_options) && $opts = Siberian_Json::decode($layout_options)) {
                $layout_options = $opts;
            } else {
                $layout_options = false;
            }

            # Homepage slider
            $homepage_slider_images = [];
            $slider_images = $application->getSliderImages();
            foreach ($slider_images as $slider_image) {
                $homepage_slider_images[] = $slider_image->getLink();
            }

            $data_homepage = [
                "pages" => $data_pages,
                "more_items" => $data_more_items,
                "customer_account" => $data_customer_account,
                "layout" => [
                    "layout_id" => "l{$application->getLayoutId()}",
                    "layout_code" => $application->getLayout()->getCode(),
                    "layout_options" => $layout_options,
                    "visibility" => $application->getLayoutVisibility(),
                    "use_horizontal_scroll" => !!$layout->getUseHorizontalScroll(),
                    "position" => $layout->getPosition()
                ],
                "limit_to" => $application->getLayout()->getNumberOfDisplayedIcons(),
                "layout_id" => "l{$application->getLayoutId()}",
                "layout_code" => $application->getLayout()->getCode(),
                "tabbar_is_transparent" => !!($background_color == "transparent"),
                "homepage_slider_is_visible" => !!$application->getHomepageSliderIsVisible(),
                "homepage_slider_duration" => $application->getHomepageSliderDuration(),
                "homepage_slider_loop_at_beginning" => !!$application->getHomepageSliderLoopAtBeginning(),
                "homepage_slider_size" => $application->getHomepageSliderSize(),
                "homepage_slider_is_new" => !!($application->getHomepageSliderSize() != null),
                "homepage_slider_images" => $homepage_slider_images,
            ];

            foreach ($application->getOptions() as $opt) {
                $data_homepage['layouts'][$opt->getValueId()] = $opt->getLayoutId();
            }

            $this->cache->save($data_homepage, $cache_id_homepage, [
                "front_mobile_home_findall",
                "app_" . $application->getId(),
                "homepage_app_" . $application->getId(),
                "css_app_" . $app_id,
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ]);

            $data_homepage["x-cache"] = "MISS";
        } else {

            $data_homepage = $result;

            $data_homepage["x-cache"] = "HIT";
        }

        /** Don't cache customer */
        $push_number = 0;
        //if ($device_uid = $this->getRequest()->getParam("device_uid")) {
        //    $message = new Push_Model_Message();
        //    $push_number = $message->countByDeviceId($device_uid);
        //}
        $data_homepage["push_badge"] = $push_number;
        /** ========== !Homepage, Layout, Features ========== */


        /** ========== Translations ========== */
        # Cache is based on locale/app_id.
        $cache_id_translation = "application_mobile_translation_findall_app_{$app_id}_locale_{$current_language}";
        if (!$result = $this->cache->load($cache_id_translation)) {

            Siberian_Cache_Translation::init();

            $data_translation = Core_Model_Translator::getTranslationsFor($application->getDesignCode());

            if (empty($data_translation)) {
                $data_translation = ["_empty-translation-cache_" => true];
            }

            $this->cache->save($data_translation, $cache_id_translation, [
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ]);

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

        $data_load["customer"] = [
            "id" => $customer_id,
            "can_connect_with_facebook" => !!$application->getFacebookId(),
            "can_access_locked_features" => !!($customer_id && $session->getCustomer()->canAccessLockedFeatures()),
            "token" => Zend_Session::getId()
        ];

        $data = [
            "load" => $data_load,
            "css" => $data_css,
            "homepage" => $data_homepage,
            "translation" => $data_translation,
        ];

        /** Force no cache */
        $response = $this->getResponse();
        $response->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $response->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $response->setHeader("Pragma", "no-cache");

        $this->_sendJson($data);
    }

    public function touchedAction()
    {

        $application = $this->getApplication();

        $option_values = $application->getPages(10);

        $touched_values = [];
        foreach ($option_values as $option_value) {
            $touched_values[$option_value->getId()] = [
                "touched_at" => $option_value->getTouchedAt() * 1,
                "expires_at" => $option_value->getExpiresAt() * 1
            ];
        }

        /** Force no cache */
        $response = $this->getResponse();
        $response->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $response->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $response->setHeader("Pragma", "no-cache");

        $data = [
            "success" => true,
            "touched" => $touched_values
        ];

        $this->_sendJson($data);
    }

    public function pagesv2Action()
    {

        $request = $this->getRequest();
        $application = $this->getApplication();
        $app_id = $application->getId();
        $current_language = Core_Model_Language::getCurrentLanguage();
        $data = [];

        /** ========== Homepage, Layout, Features ========== */
        $cache_id_homepage = "v5_front_mobile_home_findall_app_{$app_id}_locale_{$current_language}";
        if (!$result = $this->cache->load($cache_id_homepage)) {

            $option_values = $application->getPages(10, true);
            $data_pages = [];
            $color = $application->getBlock("tabbar")->getImageColor();

            foreach ($option_values as $option_value) {

                try {
                    $object = $option_value->getObject();

                    /**
                     * In-App-Browser / Browser options.
                     */
                    $hide_navbar = null;
                    $use_external_app = null;
                    if ($option_value->getCode() === "weblink_mono") {
                        $hide_navbar = $object->getHideNavbar();
                        $use_external_app = $object->getUseExternalApp();
                    }

                    if (sizeof($option_values) >= 50) {
                        if ($option_value->getCode() === "folder") {
                            $embed_payload = false;
                        } else {
                            $embed_payload = $option_value->getEmbedPayload($request);
                        }
                    } else {
                        $embed_payload = $option_value->getEmbedPayload($request);
                    }

                    $data_pages[] = [
                        "value_id" => (integer)$option_value->getId(),
                        "id" => (integer)$option_value->getId(),
                        "layout_id" => (integer)$option_value->getLayoutId(),
                        "code" => $option_value->getCode(),
                        "name" => $option_value->getTabbarName(),
                        "subtitle" => $option_value->getTabbarSubtitle(),
                        "is_active" => (boolean)$option_value->isActive(),
                        "url" => $option_value->getUrl(null, ["value_id" => $option_value->getId()], false),
                        "hide_navbar" => (boolean)$hide_navbar,
                        "use_external_app" => (boolean)$use_external_app,
                        "path" => $option_value->getPath(null, ["value_id" => $option_value->getId()], 'mobile'),
                        "icon_url" => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option_value->getIconId(), $color),
                        "icon_is_colorable" => (boolean)$option_value->getImage()->getCanBeColorized(),
                        "is_locked" => (boolean)$option_value->isLocked(),
                        "is_link" => (boolean)!$option_value->getIsAjax(),
                        "use_my_account" => (boolean)$option_value->getUseMyAccount(),
                        "use_nickname" => (boolean)$option_value->getUseNickname(),
                        "use_birthdate" => (boolean)$option_value->getUseBirthdate(),
                        "use_ranking" => (boolean)$option_value->getUseRanking(),
                        "use_civility" => (boolean)$option_value->getUseCivility(),
                        "use_mobile" => (boolean)$option_value->getUseMobile(),
                        "offline_mode" => (boolean)$option_value->getObject()->isCacheable(),
                        "custom_fields" => $option_value->getCustomFields(),
                        "embed_payload" => $embed_payload,
                        "position" => (integer)$option_value->getPosition(),
                        "homepage" => (boolean)($option_value->getFolderCategoryId() === null),
                        "touched_at" => (integer)$option_value->getTouchedAt(),
                        "expires_at" => (integer)$option_value->getExpiresAt()
                    ];
                } catch (Exception $e) {
                    # Silently fail missing modules
                    log_alert("A module is possibly missing, " . $e->getMessage());
                }
            }
            $data["pages"] = $data_pages;
            $data["x-cache"] = "MISS";
        } else {

            $data["pages"] = $result["pages"];
            $data["x-cache"] = "HIT";
        }


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
    public function loadAction()
    {
        $application = $this->getApplication();

        $cache_id = "pre4812_front_mobile_load_app_{$application->getId()}";

        if (!$result = $this->cache->load($cache_id)) {

            # Compress homepage default
            $homepage_image = Core_Model_Directory::getBasePathTo($this->getApplication()->getHomepageBackgroundImageUrl());
            $homepage_image_b64 = Siberian_Image::open($homepage_image)->cropResize(256)->inline();

            $googlemaps_key = $application->getGooglemapsKey();

            $privacy_policy = trim($application->getPrivacyPolicy());
            if (empty($privacy_policy)) {
                $privacy_policy = false;
            }

            $icon_color = strtolower($application->getAndroidPushColor());
            if (!preg_match("/^#[a-f0-9]{6}$/", $icon_color)) {

                # Fallback with a number only color ...
                $icon_color = "#808080";

            }

            $data = [
                "css" => $this->getRequest()->getBaseUrl() . Template_Model_Design::getCssPath($application),
                "application" => [
                    "id" => $application->getId(),
                    "name" => $application->getName(),
                    "is_locked" => $application->requireToBeLoggedIn(),
                    "is_bo_locked" => $application->getIsLocked(),
                    "colors" => [
                        "header" => [
                            "backgroundColor" => $application->getBlock("header")->getBackgroundColorRGB(),
                            "color" => $application->getBlock("header")->getColorRGB()
                        ],
                        "background" => [
                            "backgroundColor" => $application->getBlock("background")->getBackgroundColor(),
                            "color" => $application->getBlock("background")->getColor()
                        ],
                        "list_item" => [
                            "color" => $application->getBlock("list_item")->getColor()
                        ]
                    ],
                    "admob" => $this->__getAdmobSettings(),
                    "facebook" => [
                        "id" => $application->getFacebookId(),
                        "scope" => Customer_Model_Customer_Type_Facebook::getScope()
                    ],
                    //"gcm_senderid" => Push_Model_Certificate::getAndroidSenderId(),
                    "gcm_iconcolor" => $icon_color,
                    "googlemaps_key" => $googlemaps_key,
                    "offline_content" => ($application->getOfflineContent() == 1),
                    "ios_status_bar_is_hidden" => ($application->getIosStatusBarIsHidden() == 1),
                    "android_status_bar_is_hidden" => ($application->getAndroidStatusBarIsHidden() == 1),
                    "privacy_policy" => str_replace("#APP_NAME", $application->getName(), $privacy_policy),
                ],
                "homepage_image" => $homepage_image_b64
            ];

            $this->cache->save($data, $cache_id, [
                "front_mobile_load",
                "app_" . $application->getId()
            ]);

            $data["x-cache"] = "MISS";
        } else {
            $data = $result;

            $data["x-cache"] = "HIT";
        }

        /** Don't cache customer */
        $customer_id = $this->getSession()->getCustomerId();
        $this->__refreshFBToken($this->getSession()->getCustomer());

        $data["customer"] = [
            "id" => $customer_id,
            "can_connect_with_facebook" => !!$application->getFacebookId(),
            "can_access_locked_features" => $customer_id && $this->getSession()->getCustomer()->canAccessLockedFeatures(),
            "token" => Zend_Session::getId()
        ];

        $this->_sendJson($data);
    }

    /**
     * Progressive Web App manifest for Android/iOS
     *
     * @param $refresh
     * @return array
     */
    public function generatewebappconfig($refresh = false)
    {

        $application = $this->getApplication();
        $app_id = $application->getId();
        $app_icon = $application->getIcon();

        $base_url = $this->getRequest()->getBaseUrl();

        $manifest_name_base = Core_Model_Directory::getTmpDirectory(true) . "/webapp_manifest_{$app_id}.json";
        $manifest_name = Core_Model_Directory::getTmpDirectory() . "/webapp_manifest_{$app_id}.json";

        $app_icon_base64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($app_icon))->scaleResize(192, 192);
        $app_icon_144_base64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($app_icon))->scaleResize(144, 144);
        $app_icon_512_base64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($app_icon))->scaleResize(512, 512);
        $startup_image_base64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($application->getStartupImageUrl()))->jpeg();

        $app_icon_base64 = str_replace(Core_Model_Directory::getBasePathTo(""), $base_url . "/", $app_icon_base64->png());
        $app_icon_144_base64 = str_replace(Core_Model_Directory::getBasePathTo(""), $base_url . "/", $app_icon_144_base64->png());
        $app_icon_512_base64 = str_replace(Core_Model_Directory::getBasePathTo(""), $base_url . "/", $app_icon_512_base64->png());

        $blocks = $application->getBlocks();
        $theme_color = null;
        $general_color = null;
        foreach ($blocks as $block) {
            if ($block->getBackgroundColorVariableName() === "\$bar-custom-bg") {
                $theme_color = $block;
            }

            if ($block->getBackgroundColorVariableName() === "\$general-custom-bg") {
                $general_color = $block;
            }
        }

        if (!is_readable($manifest_name_base) || $refresh) {

            # Generate manifest
            $manifest = [
                "name" => $application->getName(),
                "short_name" => cut($application->getName(), 12, ""),
                "start_url" => "/var/apps/browser/index-prod.html#/" . $application->getKey(),
                "display" => "fullscreen",
                "icons" => [
                    [
                        "src" => $app_icon_144_base64,
                        "sizes" => "144x144",
                        "type" => "image/png",
                        "density" => 4.0,
                    ],
                    [
                        "src" => $app_icon_base64,
                        "sizes" => "192x192",
                        "type" => "image/png",
                        "density" => 4.0,
                    ],
                    [
                        "src" => $app_icon_512_base64,
                        "sizes" => "512x512",
                        "type" => "image/png",
                        "density" => 4.0,
                    ]
                ],
                "theme_color" => $theme_color->getBackgroundColor(),
                "background_color" => $general_color->getBackgroundColor()
            ];

            File::putContents($manifest_name_base, Siberian_Json::encode($manifest));
        }

        //Collect images and manifest url
        $data = [
            "startup_image_url" => $startup_image_base64,
            "icon_url" => $app_icon_base64,
            "manifest_url" => $manifest_name,
            "theme_color" => $theme_color->getBackgroundColor(),
        ];

        return $data;
    }

    public function styleAction()
    {
        $html = $this->getLayout()->addPartial('style', 'core_view_mobile_default', 'page/css.phtml')->toHtml();
        $this->getLayout()->setHtml($html);
    }

    public function backgroundimageAction()
    {

        $urls = [
            "standard" => "",
            "hd" => "",
            "tablet" => ""
        ];

        $option = $this->getCurrentOptionValue();

        if ($this->getRequest()->getParam("value_id") == "home") {
            $urls = [
                "standard" => $this->clean_url($this->getRequest()->getBaseUrl() . $this->getApplication()->getHomepageBackgroundImageUrl()),
                "hd" => $this->clean_url($this->getRequest()->getBaseUrl() . $this->getApplication()->getHomepageBackgroundImageUrl("hd")),
                "tablet" => $this->clean_url($this->getRequest()->getBaseUrl() . $this->getApplication()->getHomepageBackgroundImageUrl("tablet"))
            ];
        } else if ($option->hasBackgroundImage() AND $option->getBackgroundImage() != "no-image") {
            $urls = [
                "standard" => $this->clean_url($this->getRequest()->getBaseUrl() . $option->getBackgroundImageUrl()),
                "hd" => $this->clean_url($this->getRequest()->getBaseUrl() . $option->getBackgroundImageUrl()),
                "tablet" => $this->clean_url($this->getRequest()->getBaseUrl() . $option->getBackgroundImageUrl())
            ];
        } else if ($option->getIsHomepage() OR $this->getApplication()->getUseHomepageBackgroundImageInSubpages()) {
            $urls = [
                "standard" => $this->clean_url($this->getRequest()->getBaseUrl() . $this->getApplication()->getHomepageBackgroundImageUrl()),
                "hd" => $this->clean_url($this->getRequest()->getBaseUrl() . $this->getApplication()->getHomepageBackgroundImageUrl("hd")),
                "tablet" => $this->clean_url($this->getRequest()->getBaseUrl() . $this->getApplication()->getHomepageBackgroundImageUrl("tablet"))
            ];
        }

        $this->_sendJson($urls);

    }

    public function backgroundimagesAction()
    {
        try {
            $request = $this->getRequest();
            $base_url = $request->getBaseUrl();
            $application = $this->getApplication();
            $use_background_for_all = $application->getUseHomepageBackgroundImageInSubpages();
            $device_width = $request->getParam('device_width');
            $device_height = $request->getParam('device_height');

            if ($device_height > $device_width) {
                $ratio = $device_height / $device_width;
                $biggest = $device_height;
            } else {
                $ratio = $device_width / $device_height;
                $biggest = $device_width;
            }

            $backgrounds = [];
            $options = $application->getOptions();

            $device_resolution = 'standard';
            if ($ratio < 1.4) {
                $device_resolution = 'tablet';
            }
            if (($device_resolution === 'standard') && ($biggest > 2000)) {
                $device_resolution = 'hd';
            }
            $fallback = img_to_base64(null);

            // Homepage global!
            try {
                $backgrounds['home'] = Siberian_Image::getForMobile(
                    $base_url,
                    Core_Model_Directory::getBasePathTo($application->getHomepageBackgroundImageUrl($device_resolution))
                );
                $backgrounds['landscape_home'] = Siberian_Image::getForMobile(
                    $base_url,
                    Core_Model_Directory::getBasePathTo($application->getHomepageBackgroundImageUrl('landscape_' . $device_resolution))
                );
            } catch (Exception $e) {
                $backgrounds['home'] = $fallback;
                $backgrounds['landscape_home'] = $fallback;
            }

            foreach ($options as $option) {
                $background = null;

                $value_id = $option->getId();
                if ($option->hasBackgroundImage() &&
                    ($option->getBackgroundImage() !== 'no-image') &&
                    ($option->getBackgroundImage() !== '')) {

                    try {
                        $background = Siberian_Image::getForMobile(
                            $base_url,
                            Core_Model_Directory::getBasePathTo($option->getBackgroundImageUrl())
                        );
                    } catch (Exception $e) {
                        $background = $fallback;
                    }

                    try {
                        $landscape_background = Siberian_Image::getForMobile(
                            $base_url,
                            Core_Model_Directory::getBasePathTo($option->getBackgroundLandscapeImageUrl())
                        );
                    } catch (Exception $e) {
                        // Landscape fallback is portrait!
                        $landscape_background = $background;
                    }

                } else if ($option->getIsHomepage() || $use_background_for_all) {
                    $background = $backgrounds["home"];
                    $landscape_background = $backgrounds["landscape_home"];
                }

                if (!empty($background)) {
                    $backgrounds[$value_id] = $background;
                    $backgrounds['landscape_' . $value_id] = ($landscape_background === null) ? $background : $landscape_background;
                }
            }

            $payload = [
                'success' => true,
                'backgrounds' => $backgrounds
            ];

            if (Siberian_Debug::isDevelopment()) {
                $payload['debug'] = [
                    'type' => $device_resolution,
                    'ratio' => $ratio,
                    'biggest' => $biggest
                ];
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => __('Unable to fetch your application background images.')
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @deprecated and possibly not used
     *
     * @return string
     */
    protected function _getBackgroundImage()
    {
        $url = '';
        $option = $this->getCurrentOptionValue();

        if ($option->getIsHomepage()) {
            $url = $this->getApplication()->getBackgroundImageUrl('retina4');
        } else if ($option->getHasBackgroundImage()) {
            $url = $option->getBackgroundImageUrl();
        } else if ($option->getUseHomepageBackgroundImage()) {
            $url = $this->getApplication()->getHomepageBackgroundImageUrl('retina');
        }

        return $url;
    }

    private function __getAdmobSettings()
    {

        $application = $this->getApplication();

        $subscription = null;
        $planUseAds = null;
        if ($this->isPe()) {
            $subscription = $application->getSubscription()->getSubscription();
            $planUseAds = $subscription->getUseAds();
        }

        $device = $this->getDevice()->isIosdevice() ? $application->getDevice(1) : $application->getDevice(2);

        if ($this->getApplication()->getOwnerUseAds()) {

            $settings = [
                "id" => $device->getOwnerAdmobId(),
                "type" => $device->getOwnerAdmobType()
            ];

        } else {
            if ($planUseAds) {

                $settings = [
                    "id" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_id"),
                    "type" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_type")
                ];

            } else {

                if (System_Model_Config::getValueFor("application_owner_use_ads")) {

                    $settings = [
                        "id" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_id"),
                        "type" => System_Model_Config::getValueFor("application_" . $device->getType()->getOsName() . "_owner_admob_type")
                    ];

                } else {

                    $settings = [
                        "id" => $device->getAdmobId(),
                        "type" => $device->getAdmobType()
                    ];

                }
            }
        }

        return $settings;
    }

    private function __getAdmobSettingsV2()
    {

        /**
         * $application: {
         *  use_ads > application ads
         *  owner_use_ads > backoffice specific ads
         *   - system_config > default platform ads
         * }
         */

        $payload = [
            "ios_weight" => [
                "app" => 1,
                "platform" => 0,
            ],
            "android_weight" => [
                "app" => 1,
                "platform" => 0,
            ],
            "app" => [
                "ios" => [
                    "banner_id" => false,
                    "interstitial_id" => false,
                    "banner" => false,
                    "interstitial" => false,
                    "videos" => false,
                ],
                "android" => [
                    "banner_id" => false,
                    "interstitial_id" => false,
                    "banner" => false,
                    "interstitial" => false,
                    "videos" => false,
                ],
            ],
            "platform" => [
                "ios" => [
                    "banner_id" => false,
                    "interstitial_id" => false,
                    "banner" => false,
                    "interstitial" => false,
                    "videos" => false,
                ],
                "android" => [
                    "banner_id" => false,
                    "interstitial_id" => false,
                    "banner" => false,
                    "interstitial" => false,
                    "videos" => false,
                ],
            ]
        ];

        $application = $this->getApplication();

        $subscription = null;
        $planUseAds = false;
        if ($this->isPe()) {
            $subscription = $application->getSubscription()->getSubscription();
            $planUseAds = $subscription->getUseAds();
        }

        $ios_device = $application->getDevice(1);
        $android_device = $application->getDevice(2);

        # Platform/Subscription settings
        if ($application->getOwnerUseAds()) {

            $ios_types = explode("-", $ios_device->getOwnerAdmobType());
            $ios_weight = (integer)$ios_device->getOwnerAdmobWeight();
            $android_types = explode("-", $android_device->getOwnerAdmobType());
            $android_weight = (integer)$android_device->getOwnerAdmobWeight();

            $payload["platform"] = [
                "ios" => [
                    "banner_id" => $ios_device->getOwnerAdmobId(),
                    "interstitial_id" => $ios_device->getOwnerAdmobInterstitialId(),
                    "banner" => (boolean)in_array("banner", $ios_types),
                    "interstitial" => (boolean)in_array("interstitial", $ios_types),
                    "videos" => (boolean)in_array("videos", $ios_types), # Prepping the future.
                ],
                "android" => [
                    "banner_id" => $android_device->getOwnerAdmobId(),
                    "interstitial_id" => $android_device->getOwnerAdmobInterstitialId(),
                    "banner" => (boolean)in_array("banner", $android_types),
                    "interstitial" => (boolean)in_array("interstitial", $android_types),
                    "videos" => (boolean)in_array("videos", $android_types), # Prepping the future.
                ],
            ];

            if (($ios_weight >= 0) && ($ios_weight <= 100)) {
                $weight = ($ios_weight / 100);
                $payload["ios_weight"]["platform"] = $weight;
                $payload["ios_weight"]["app"] = (1 - $weight);
            }

            if (($android_weight >= 0) && ($android_weight <= 100)) {
                $weight = ($android_weight / 100);
                $payload["android_weight"]["platform"] = $weight;
                $payload["android_weight"]["app"] = (1 - $weight);
            }

        } else if (($planUseAds || System_Model_Config::getValueFor("application_owner_use_ads"))) {

            $ios_key = "application_" . $ios_device->getType()->getOsName() . "_owner_admob_%s";
            $android_key = "application_" . $android_device->getType()->getOsName() . "_owner_admob_%s";

            $ios_types = explode("-", System_Model_Config::getValueFor(sprintf($ios_key, "type")));
            $ios_weight = (integer)System_Model_Config::getValueFor(sprintf($ios_key, "weight"));
            $android_types = explode("-", System_Model_Config::getValueFor(sprintf($android_key, "type")));
            $android_weight = (integer)System_Model_Config::getValueFor(sprintf($android_key, "weight"));

            $payload["platform"] = [
                "ios" => [
                    "banner_id" => System_Model_Config::getValueFor(sprintf($ios_key, "id")),
                    "interstitial_id" => System_Model_Config::getValueFor(sprintf($ios_key, "interstitial_id")),
                    "banner" => (boolean)in_array("banner", $ios_types),
                    "interstitial" => (boolean)in_array("interstitial", $ios_types),
                    "videos" => (boolean)in_array("videos", $ios_types), # Prepping the future.
                ],
                "android" => [
                    "banner_id" => System_Model_Config::getValueFor(sprintf($android_key, "id")),
                    "interstitial_id" => System_Model_Config::getValueFor(sprintf($android_key, "interstitial_id")),
                    "banner" => (boolean)in_array("banner", $android_types),
                    "interstitial" => (boolean)in_array("interstitial", $android_types),
                    "videos" => (boolean)in_array("videos", $android_types), # Prepping the future.
                ],
            ];

            if (($ios_weight >= 0) && ($ios_weight <= 100)) {
                $weight = ($ios_weight / 100);
                $payload["ios_weight"]["platform"] = $weight;
                $payload["ios_weight"]["app"] = (1 - $weight);
            }

            if (($android_weight >= 0) && ($android_weight <= 100)) {
                $weight = ($android_weight / 100);
                $payload["android_weight"]["platform"] = $weight;
                $payload["android_weight"]["app"] = (1 - $weight);
            }

        }

        if ($application->getUseAds()) {

            $ios_types = explode("-", $ios_device->getAdmobType());
            $android_types = explode("-", $android_device->getAdmobType());

            $payload["app"] = [
                "ios" => [
                    "banner_id" => $ios_device->getAdmobId(),
                    "interstitial_id" => $ios_device->getAdmobInterstitialId(),
                    "banner" => (boolean)in_array("banner", $ios_types),
                    "interstitial" => (boolean)in_array("interstitial", $ios_types),
                    "videos" => (boolean)in_array("videos", $ios_types), # Prepping the future.
                ],
                "android" => [
                    "banner_id" => $android_device->getAdmobId(),
                    "interstitial_id" => $android_device->getAdmobInterstitialId(),
                    "banner" => (boolean)in_array("banner", $android_types),
                    "interstitial" => (boolean)in_array("interstitial", $android_types),
                    "videos" => (boolean)in_array("videos", $android_types), # Prepping the future.
                ],
            ];
        } else {
            # If user don't use admob, split revenue is 100% for platform.
            $payload["ios_weight"]["platform"] = 1;
            $payload["ios_weight"]["app"] = 0;
            $payload["android_weight"]["platform"] = 1;
            $payload["android_weight"]["app"] = 0;
        }

        return $payload;
    }

    /** Refresh the FB Token on login, and update the customer_social table. */
    private function __refreshFBToken($customer)
    {
        $customer_fb_datas = $customer->getSocialDatas("facebook");

        if (!empty($customer_fb_datas) && isset($customer_fb_datas["datas"])) {
            $social_datas = unserialize($customer_fb_datas["datas"]);
            if (isset($social_datas["access_token"])) {
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
