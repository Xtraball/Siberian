<?php

/**
 * Class Front_Controller_Api_V5
 */
class Front_Controller_Api_V5 extends Front_Controller_Api_V4
{
    /**
     * @var string
     */
    public $version = "v5";

    /**
     * @param $application
     * @param $currentLanguage
     * @param $request
     * @return array|false|string
     * @throws Zend_Exception
     */
    public function _featureBlock($application, $currentLanguage, $request)
    {
        $appVersion = $request->getBodyParams()["version"];

        $appId = $application->getId();
        $appKey = $application->getKey();
        $cacheId = 'v4_front_mobile_home_findall_app_' . $appId . '_locale_' . $currentLanguage;
        $blockStart = microtime(true);
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

                    if (sizeof($optionValues) >= 50) {
                        if (in_array($optionValue->getCode(), ["folder", "folder_v2", "custom_page"])) {
                            $embedPayload = false;
                        } else {
                            $embedPayload = $optionValue->getEmbedPayload($request);
                        }
                    } else {
                        $embedPayload = $optionValue->getEmbedPayload($request);
                    }

                    // End link special code!
                    $blockData = [
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
                        ], 'mobile'),
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

                    if ($object->getLink() &&
                        $optionValue->getCode() === "weblink_mono") {

                        $objectLink = $object->getLink();

                        // Pre 4.18.3
                        $blockData['hide_navbar'] = $objectLink->getHideNavbar();
                        $blockData['use_external_app'] = $objectLink->getUseExternalApp();

                        // Post 4.18.3
                        $blockData['link_url'] = (string) $objectLink->getData('url');
                        $blockData['external_browser'] = (boolean) $objectLink->getExternalBrowser();
                        $blockData['options'] = $objectLink->getOptions();
                    }

                    $featureBlock[] = $blockData;

                } catch (\Exception $e) {
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

            // My Account feature (if it exists)
            $myAccountOption = (new Application_Model_Option())->find("tabbar_account", "code");
            $myAccount = (new Application_Model_Option_Value())->find([
                "option_id" => $myAccountOption->getOptionId(),
                "app_id" => $appId,
            ]);

            $defaultSettings = [
                "settings" => [
                    "enable_facebook_login" => true,
                    "enable_registration" => true,
                ],
            ];
            $myAccountSettings = $defaultSettings;
            if ($myAccount->getId()) {
                try {
                    $myAccountSettings["settings"] = Json::decode($myAccount->getSettings());
                } catch (\Exception $e) {
                    $myAccountSettings = $defaultSettings;
                }
            }

            $dataHomepage = [
                'pages' => $featureBlock,
                'touched' => $touchedValues,
                'more_items' => $dataMoreItems,
                'myAccount' => $myAccountSettings,
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
                $this->version,
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

        // Dynamic patches (non-cached) for specific app versions!
        if (version_compare($appVersion, "4.15.6", "<")) {
            # 1. Places
            foreach ($dataHomepage["pages"] as &$page) {
                if ($page["code"] === "places") {
                    $page["path"] = sprintf("/%s/places/mobile_list/index/value_id/%s",
                        $appKey,
                        $page["value_id"]);
                }
            }
        }

        if (version_compare($appVersion, "4.18.1", "<")) {
            # 2. M-Commerce
            foreach ($dataHomepage["pages"] as &$page) {
                if ($page["code"] === "m_commerce") {
                    $page["path"] = sprintf("/%s/mcommerce/mobile_category/index/value_id/%s",
                        $appKey,
                        $page["value_id"]);
                }
            }
        }

        if (version_compare($appVersion, '4.18.3', '>=')) {
            # 3. WebLink
            foreach ($dataHomepage['pages'] as &$page) {
                if ($page['code'] === 'weblink_mono') {
                    $page['url'] = $page['link_url'];
                }
            }
        }
        // Dynamic patches (non-cached) for specific app versions!

        // Never cache customer data!
        $pushNumber = 0;
        $deviceUid = $request->getParam('device_uid', null);
        if (!empty($deviceUid)) {
            $pushNumber = (new Push_Model_Message())
                ->countByDeviceId($deviceUid);
        }
        $dataHomepage['push_badge'] = $pushNumber;

        // Time to generate the current block!
        $dataHomepage['x-delay'] = microtime(true) - $blockStart;

        return $dataHomepage;
    }
}
