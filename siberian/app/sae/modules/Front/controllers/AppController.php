<?php

/**
 * Class Front_AppController
 */
class Front_AppController extends Front_Controller_App_Default
{
    /**
     * Here we generate the Application initial payload
     * its composed of the following blocks
     * - Application
     * - CSS
     * - Features / Options
     * - Translations
     * - Customer (never cached)
     * 
     * @throws Zend_Exception
     */
    public function initAction()
    {

        /** Caching each block independently, to optimize loading */

        $application = $this->getApplication();
        $appId = $application->getId();
        $request = $this->getRequest();
        $currentLanguage = Core_Model_Language::getCurrentLanguage();

        $cssBlock = $this->_cssBlock($application);
        $loadBlock = $this->_loadBlock($application);
        $featureBlock = $this->_featureBlock($application, $currentLanguage, $request);





        /** ========== Translations ========== *
        # Cache is based on locale/app_id.
        $cache_id_translation = "v3_application_mobile_translation_findall_app_{$appId}_locale_{$currentLanguage}";
        if (!$result = $this->cache->load($cache_id_translation)) {

            Siberian_Cache_Translation::init();

            $data_translation = Core_Model_Translator::getTranslationsFor($application->getDesignCode());

            if (empty($data_translation)) {
                $data_translation = ["_empty-translation-cache_" => true];
            }

            $this->cache->save($data_translation, $cache_id_translation, [
                "v3",
                "mobile_translation",
                "mobile_translation_locale_{$currentLanguage}"
            ]);

            $data_translation["x-cache"] = "MISS";
        } else {

            $data_translation = $result;

            $data_translation["x-cache"] = "HIT";
        }
        $data_translation["_locale"] = strtolower(str_replace("_", "-", $currentLanguage));
        /** ========== !Translations ========== *


        /** Don't cache customer *
        $session = $this->getSession();
        $customer = $session->getCustomer();
        $customer_id = $customer->getCustomerId();
        $this->__refreshFBToken($customer);

        $is_logged_in = false;

        $loadBlock["customer"] = [
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

            $loadBlock["customer"] = array_merge($loadBlock["customer"], [
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

        $loadBlock["customer"] = array_merge($loadBlock["customer"], [
            "is_logged_in" => $is_logged_in
        ]);*

        /** Get the most recent cache update *
        $updated_at = max([
            $this->cache->test($cacheIdCss),
            $this->cache->test($cacheId),
            $this->cache->test($cacheId),
            $this->cache->test($cache_id_translation),
        ]);

        /** Web App manifest *
        $data_manifest = $this->generatewebappconfig();
         *
         * $data = [
        "load" => $loadBlock,
        "css" => $data_css,
        "homepage" => $dataHomepage,
        "manifest" => $data_manifest,
        "translation" => $data_translation,
        ];

         * */
        $data = [
            'cssBlock' => $cssBlock,
            'loadBlock' => $loadBlock,
            'featureBlock' => $featureBlock,
        ];

        /** Force no cache */
        $response = $this->getResponse();
        $response->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $response->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $response->setHeader("Pragma", "no-cache");
        //$response->setHeader("X-Cache-Last-Update", $updated_at);

        $this->_sendJson($data);
    }

    /**
     * @param $application
     * @return array|false|string
     */
    private function _cssBlock ($application)
    {
        $cacheIdCss = 'v4_front_mobile_load_css_app_' . $application->getId();
        if (!$result = $this->cache->load($cacheIdCss)) {
            $cssFile = Core_Model_Directory::getBasePathTo(Template_Model_Design::getCssPath($application));
            $blockCss = [
                'css' => file_get_contents($cssFile)
            ];

            $this->cache->save($blockCss, $cacheIdCss, [
                'v4',
                'front_mobile_load_css',
                'css_app_' . $application->getId()
            ]);

            unset($cssFile);
            unset($cacheIdCss);

            $blockCss['x-cache'] = 'MISS';
        } else {
            $blockCss = $result;
            $blockCss['x-cache'] = 'HIT';
        }
        return $blockCss;
    }

    /**
     * @param $application
     * @return array|false|string
     */
    private function _loadBlock ($application)
    {
        $appId = $application->getId();
        $cacheId = 'v4_front_mobile_load_app_' . $appId;
        if (!$result = $this->cache->load($cacheId)) {

            // Homepage image url!
            $homepageImage = Core_Model_Directory::getBasePathTo($application->getHomepageBackgroundImageUrl());
            $googleMapsKey = $application->getGooglemapsKey();

            $privacyPolicy = trim($application->getPrivacyPolicy());
            if (empty($privacyPolicy)) {
                $privacyPolicy = false;
            }

            $privacyPolicyTitle = trim($application->getPrivacyPolicyTitle());
            if (empty($privacyPolicyTitle)) {
                $privacyPolicyTitle = __('Privacy policy');
            }

            $iconColor = strtolower($application->getAndroidPushColor());
            if (!preg_match('/^#[a-f0-9]{6}$/', $iconColor)) {
                // Fallback with a number only color!
                $iconColor = '#808080';
            }

            $progressbarColor = $application->getBlock('dialog_text')->getColor();
            $progressbarTrailColor = $application->getBlock('dialog_bg')->getColor();
            $progressbarColor = Siberian_Color::newColor($progressbarColor, 'hex');
            $progressbarTrailColor = Siberian_Color::newColor($progressbarTrailColor, 'hex');

            if ($progressbarTrailColor->lightness > 80) {
                $progressbarTrailColor = $progressbarTrailColor
                    ->getNew('lightness', $progressbarColor->lightness - 20);
            } else {
                $progressbarTrailColor = $progressbarTrailColor
                    ->getNew('lightness', $progressbarColor->lightness + 20);
            }

            $bgBlock = $application->getBlock('background');
            $bgColorHex = $bgBlock->getBackgroundColor();
            $bgColor = Siberian_Color::newColor($bgColorHex, 'hex');
            $bgColor->alpha = $bgBlock->getBackgroundOpacity() / 100;

            $colorStatusBar = Siberian_Color::newColor($application->getBlock('header')->getBackgroundColor(), 'hex');
            $colorStatusBarLighten = $colorStatusBar->getNew('lightness', $colorStatusBar->lightness - 10);

            $loadBlock = [
                'application' => [
                    'id' => $appId,
                    'name' => $application->getName(),
                    'is_locked' => (boolean)$application->requireToBeLoggedIn(),
                    'is_bo_locked' => (boolean)$application->getIsLocked(),
                    'colors' => [
                        'header' => [
                            'statusBarColor' => $colorStatusBarLighten->toCSS('hex'),
                            'backgroundColor' => $application->getBlock('header')->getBackgroundColorRGB(),
                            'color' => $application->getBlock('header')->getColorRGB()
                        ],
                        'background' => [
                            'backgroundColor' => $bgColorHex,
                            'color' => $application->getBlock('background')->getColor(),
                            'rgba' => $bgColor->toCSS('rgba')
                        ],
                        'loader' => [
                            'trail' => $progressbarTrailColor->toCSS('hex'),
                            'bar_text' => $progressbarColor->toCSS('hex'),
                        ],
                        'list_item' => [
                            'color' => $application->getBlock('list_item')->getColor()
                        ]
                    ],
                    //'admob' => $this->__getAdmobSettingsV2(),
                    'facebook' => [
                        'id' => empty($application->getFacebookId()) ? null : $application->getFacebookId(),
                        'scope' => Customer_Model_Customer_Type_Facebook::getScope()
                    ],
                    'pushIconcolor' => $iconColor,
                    'gmapsKey' => $googleMapsKey,
                    'offlineContent' => (boolean)$application->getOfflineContent(),
                    'iosStatusBarIsHidden' => (boolean)$application->getIosStatusBarIsHidden(),
                    'androidStatusBarIsHidden' => (boolean)$application->getAndroidStatusBarIsHidden(),
                    'privacyPolicy' => [
                        'title' => $privacyPolicyTitle,
                        'text' => str_replace('#APP_NAME', $application->getName(), $privacyPolicy),
                        'gdpr' => $application->getPrivacyPolicyGdpr(),
                    ],
                    'gdpr' => [
                        'isEnabled' => isGdpr(),
                    ],
                    'useHomepageBackground' => (boolean) $application->getUseHomepageBackgroundImageInSubpages(),
                    'backButton' => (string)$application->getBackButton(),
                ],
                'homepageImage' => $homepageImage
            ];
            $this->cache->save($loadBlock, $cacheId, [
                'v4',
                'front_mobile_load',
                'app_' . $appId
            ]);

            // Free!
            unset($cacheId);
            unset($appId);
            unset($homepageImage);
            unset($googleMapsKey);
            unset($privacyPolicy);
            unset($privacyPolicyTitle);
            unset($iconColor);
            unset($progressbarColor);
            unset($progressbarTrailColor);
            unset($progressbarColor);
            unset($progressbarTrailColor);
            unset($bgBlock);
            unset($bgColorHex);
            unset($bgColor);
            unset($colorStatusBar);
            unset($colorStatusBarLighten);

            $loadBlock['x-cache'] = 'MISS';
        } else {
            $loadBlock = $result;
            $loadBlock['x-cache'] = 'HIT';
        }

        return $loadBlock;
    }

    private function _featureBlock($application, $currentLanguage, $request)
    {
        $appId = $application->getId();
        $cacheId = 'v4_front_mobile_home_findall_app_' . $appId . '_locale_' . $currentLanguage;
        if (!$result = $this->cache->load($cacheId)) {
            $optionValues = $application->getPages(10, true);
            $featureBlock = [];
            $color = $application->getBlock('tabbar')->getImageColor();
            $backgroundColor = $application->getBlock('tabbar')->getBackgroundColor();

            $touchedValues = [];
            foreach ($optionValues as $optionValue) {
                $touchedValues[$optionValue->getId()] = [
                    'touched_at' => (integer)$optionValue->getTouchedAt(),
                    'expires_at' => (integer)$optionValue->getExpiresAt()
                ];

                try {
                    $object = $optionValue->getObject();

                    // In-App-Browser / Browser options!
                    $hideNavbar = null;
                    $useExternalApp = null;
                    if ($optionValue->getCode() === 'weblink_mono') {
                        $hideNavbar = $object->getLink()->getHideNavbar();
                        $useExternalApp = $object->getLink()->getUseExternalApp();
                    }

                    if (sizeof($optionValues) >= 50) {
                        if ($optionValue->getCode() === 'folder') {
                            $embedPayload = false;
                        } else {
                            $embedPayload = $optionValue->getEmbedPayload($request);
                        }
                    } else {
                        $embedPayload = $optionValue->getEmbedPayload($request);
                    }

                    // End link special code!
                    $featureBlock[] = [
                        'value_id' => (integer) $optionValue->getId(),
                        'id' => (integer) $optionValue->getId(),
                        'layout_id' => (integer) $optionValue->getLayoutId(),
                        'code' => $optionValue->getCode(),
                        'name' => $optionValue->getTabbarName(),
                        'subtitle' => $optionValue->getTabbarSubtitle(),
                        'is_active' => (boolean) $optionValue->isActive(),
                        'url' => $optionValue->getUrl(null, [
                            'value_id' => $optionValue->getId()
                        ], false),
                        'hide_navbar' => (boolean) $hideNavbar,
                        'use_external_app' => (boolean) $useExternalApp,
                        'path' => $optionValue->getPath(null, [
                            'value_id' => $optionValue->getId()
                        ], false),
                        'icon_url' => $request->getBaseUrl() . $this->_getColorizedImage($optionValue->getIconId(), $color),
                        'icon_is_colorable' => (boolean) $optionValue->getImage()->getCanBeColorized(),
                        'is_locked' => (boolean) $optionValue->isLocked(),
                        'is_link' => !(boolean) $optionValue->getIsAjax(),
                        'use_my_account' => (boolean) $optionValue->getUseMyAccount(),
                        'use_nickname' => (boolean) $optionValue->getUseNickname(),
                        'use_ranking' => (boolean) $optionValue->getUseRanking(),
                        'offline_mode' => (boolean) $optionValue->getObject()->isCacheable(),
                        'custom_fields' => $optionValue->getCustomFields(),
                        'embed_payload' => $embedPayload,
                        'position' => (integer) $optionValue->getPosition(),
                        'homepage' => (boolean) ($optionValue->getFolderCategoryId() === null),
                        'touched_at' => (integer) $optionValue->getTouchedAt(),
                        'expires_at' => (integer) $optionValue->getExpiresAt()
                    ];
                } catch (Exception $e) {
                    // Silently fail missing modules!
                    log_alert('A module is probably missing, ' . $e->getMessage());
                }
            }

            $option = (new Application_Model_Option())
                ->findTabbarMore();

            $moreColorizable = true;
            if ($application->getMoreIconId()) {
                $icon = (new Media_Model_Library_Image())
                    ->find($application->getMoreIconId());
                if (!$icon->getCanBeColorized()) {
                    $moreColor = null;
                } else {
                    $moreColor = $color;
                }

                $moreColorizable = $icon->getCanBeColorized();
            } else {
                $moreColor = $color;
            }

            $dataMoreItems = [
                'code' => $option->getCode(),
                'name' => $option->getTabbarName(),
                'subtitle' => $application->getMoreSubtitle(),
                'is_active' => (boolean) $option->isActive(),
                'url' => '',
                'icon_url' => $request->getBaseUrl() .
                    $this->_getColorizedImage($option->getIconUrl(), $moreColor),
                'icon_is_colorable' => (boolean) $moreColorizable,
            ];

            $option = (new Application_Model_Option())
                ->findTabbarAccount();

            $accountColorizable = true;
            if ($application->getAccountIconId()) {
                $library = new Media_Model_Library_Image();
                $icon = $library->find($application->getAccountIconId());
                if (!$icon->getCanBeColorized()) {
                    $accountColor = null;
                } else {
                    $accountColor = $color;
                }

                $accountColorizable = $icon->getCanBeColorized();
            } else {
                $accountColor = $color;
            }

            $dataCustomerAccount = [
                'code' => $option->getCode(),
                'name' => $option->getTabbarName(),
                'subtitle' => $application->getAccountSubtitle(),
                'is_active' => (boolean)$option->isActive(),
                'url' => $this->getUrl('customer/mobile_account_login'),
                'path' => $this->getPath('customer/mobile_account_login'),
                'login_url' => $this->getUrl('customer/mobile_account_login'),
                'login_path' => $this->getPath('customer/mobile_account_login'),
                'edit_url' => $this->getUrl('customer/mobile_account_edit'),
                'edit_path' => $this->getPath('customer/mobile_account_edit'),
                'icon_url' => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $accountColor),
                'icon_is_colorable' => (boolean)$accountColorizable,
                'is_visible' => (boolean)$application->usesUserAccount()
            ];

            $layout = new Application_Model_Layout_Homepage();
            $layout->find($application->getLayoutId());

            $layoutOptions = $application->getLayoutOptions();
            if (!empty($layoutOptions) && $opts = Siberian_Json::decode($layoutOptions)) {
                $layoutOptions = $opts;
            } else {
                $layoutOptions = false;
            }

            # Homepage slider
            $homepageSliderImages = [];
            $sliderImages = $application->getSliderImages();
            foreach ($sliderImages as $sliderImage) {
                $homepageSliderImages[] = $sliderImage->getLink();
            }

            $dataHomepage = [
                'pages' => $featureBlock,
                'touched' => $touchedValues,
                'more_items' => $dataMoreItems,
                'customer_account' => $dataCustomerAccount,
                'layout' => [
                    'layout_id' => 'l' . $application->getLayoutId(),
                    'layout_code' => $application->getLayout()->getCode(),
                    'layout_options' => $layoutOptions,
                    'visibility' => $application->getLayoutVisibility(),
                    'use_horizontal_scroll' => (boolean) $layout->getUseHorizontalScroll(),
                    'position' => $layout->getPosition()
                ],
                'limit_to' => (integer) $application->getLayout()->getNumberOfDisplayedIcons(),
                'layout_id' => 'l' . $application->getLayoutId(),
                'layout_code' => $application->getLayout()->getCode(),
                'tabbar_is_transparent' => (boolean) ($backgroundColor === 'transparent'),
                'homepage_slider_is_visible' => (boolean) $application->getHomepageSliderIsVisible(),
                'homepage_slider_duration' => $application->getHomepageSliderDuration(),
                'homepage_slider_loop_at_beginning' => (boolean) $application->getHomepageSliderLoopAtBeginning(),
                'homepage_slider_size' => $application->getHomepageSliderSize(),
                'homepage_slider_opacity' => (integer) $application->getHomepageSliderOpacity(),
                'homepage_slider_offset' => (integer) $application->getHomepageSliderOffset(),
                'homepage_slider_is_new' => (boolean) ($application->getHomepageSliderSize() != null),
                'homepage_slider_images' => $homepageSliderImages,
            ];

            foreach ($application->getOptions() as $opt) {
                $dataHomepage['layouts'][$opt->getValueId()] = $opt->getLayoutId();
            }

            $this->cache->save($dataHomepage, $cacheId, [
                'v4',
                'front_mobile_home_findall',
                'app_' . $appId,
                'homepage_app_' . $appId,
                'css_app_' . $appId,
                'mobile_translation',
                'mobile_translation_locale_' . $currentLanguage
            ]);

            $dataHomepage['x-cache'] = 'MISS';
        } else {
            $dataHomepage = $result;
            $dataHomepage['x-cache'] = 'HIT';
        }

        // Don't cache customer informations!
        $pushNumber = 0;
        $deviceUid = $request->getParam('device_uid', null);
        if (!empty($deviceUid)) {
            $pushNumber = (new Push_Model_Message())
                ->countByDeviceId($deviceUid);
        }
        $dataHomepage['push_badge'] = $pushNumber;

        return $dataHomepage;
    }
}
