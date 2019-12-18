<?php

use Siberian\File;
use Siberian\Hook;
use Siberian\Account;
use Siberian\Json;

/**
 * Class Front_Controller_Api_Base
 */
class Front_Controller_Api_Base extends Front_Controller_App_Default
{
    /**
     * @var string
     */
    public $version = "v_base";

    /**
     * Here we generate the Application initial payload
     * its composed of the following blocks
     * - Application
     * - CSS
     * - Features / Options
     * - Translations
     * - Customer (never cached)
     */
    public function initAction()
    {
        /** Caching each block independently, to optimize loading */
        $application = $this->getApplication();
        $application->checkForUpgrades();

        $appId = $application->getId();
        $request = $this->getRequest();
        $session = $this->getSession();
        $currentLanguage = Core_Model_Language::getCurrentLanguage();

        try {
            $cssBlock = $this->_cssBlock($application);
        } catch (\Exception $e) {
            // Exception CSS
        }

        try {
            $loadBlock = $this->_loadBlock($application);
        } catch (\Exception $e) {
            // Exception CSS
        }

        try {
            $featureBlock = $this->_featureBlock($application, $currentLanguage, $request);
        } catch (\Exception $e) {
            // Exception CSS
        }

        try {
            $translationBlock = $this->_translationBlock($application, $currentLanguage);
        } catch (\Exception $e) {
            // Exception CSS
        }

        try {
            // Alter the loadBlock with the customer
            $loadBlock = $this->_customerBlock($application, $loadBlock);
        } catch (\Exception $e) {
            // Exception CSS
        }

        try {
            // Web App manifest file & informations!
            $manifestBlock = $this->_manifestBlock($application, $request);
        } catch (\Exception $e) {
            // Exception CSS
        }

        $data = [
            'cssBlock' => $cssBlock,
            'loadBlock' => $loadBlock,
            'featureBlock' => $featureBlock,
            'translationBlock' => $translationBlock,
            'manifestBlock' => $manifestBlock,
        ];

        // Init is ready, trigger the hook
        $data = Hook::trigger("app.init.ready", $data);

        /** Force no cache */
        $response = $this->getResponse();
        $response->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $response->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $response->setHeader("Pragma", "no-cache");

        $this->_sendJson($data);
    }

    /**
     * @param $application
     * @return array|false|string
     */
    public function _cssBlock ($application)
    {
        $cacheIdCss = 'v4_front_mobile_load_css_app_' . $application->getId();
        $blockStart = microtime(true);
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
        // Time to generate the current block!
        $blockCss['x-delay'] = microtime(true) - $blockStart;

        return $blockCss;
    }

    /**
     * @param $application
     * @return array|false|string
     * @throws Zend_Exception
     */
    public function _loadBlock ($application)
    {
        $appId = $application->getId();
        $cacheId = 'v4_front_mobile_load_app_' . $appId;
        $blockStart = microtime(true);
        if (!$result = $this->cache->load($cacheId)) {

            // Homepage image url!
            if ($application->getSplashVersion() == '2') {
                $homepageImage = path($application->getHomepageBackgroundUnified());
            } else {
                $homepageImage = path($application->getHomepageBackgroundImageUrl());
            }

            $homepageImageB64 = Siberian_Image::open($homepageImage)
                ->cropResize(512)->inline('jpeg', 65);

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

            $credentials = (new Push_Model_Firebase())->find(0, 'admin_id');

            $colorStatusBar = Siberian_Color::newColor($application->getBlock('header')->getBackgroundColor(), 'hex');
            $colorStatusBarLighten = $colorStatusBar->getNew('lightness', $colorStatusBar->lightness - 10);

            // My Account feature (if it exists)
            $myAccountOption = (new Application_Model_Option())->find("tabbar_account", "code");
            $myAccountFeature = (new Application_Model_Option_Value())->find([
                "option_id" => $myAccountOption->getOptionId(),
                "app_id" => $appId,
            ]);

            $defaultSettings = [
                "title" => $myAccountFeature->getTabbarName(),
                "settings" => [
                    "enable_facebook_login" => true,
                    "enable_registration" => true,
                ],
            ];
            $myAccount = $defaultSettings;
            if ($myAccountFeature->getId()) {
                try {
                    $myAccount["settings"] = Json::decode($myAccountFeature->getSettings());
                } catch (\Exception $e) {
                    $myAccount = $defaultSettings;
                }
            }

            $loadBlock = [
                'application' => [
                    'id' => $appId,
                    'name' => $application->getName(),
                    'is_locked' => (boolean)$application->requireToBeLoggedIn(),
                    'is_bo_locked' => (boolean)$application->getIsLocked(),
                    'colors' => [
                        'header' => [
                            'statusBarColor' => $colorStatusBarLighten->toCSS('hex'),
                            'backgroundColorHex' => $application->getBlock('header')->getBackgroundColor(),
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
                    'admob' => $this->_admobSettings($application),
                    'facebook' => [
                        'id' => empty($application->getFacebookId()) ? null : $application->getFacebookId(),
                        'scope' => Customer_Model_Customer_Type_Facebook::getScope()
                    ],
                    'pushIconcolor' => $iconColor,
                    'gmapsKey' => $googleMapsKey,
                    'offlineContent' => (boolean) $application->getOfflineContent(),
                    'fcmSenderID' => $credentials->getSenderId(),
                    'iosStatusBarIsHidden' => (boolean) $application->getIosStatusBarIsHidden(),
                    'androidStatusBarIsHidden' => (boolean) $application->getAndroidStatusBarIsHidden(),
                    'privacyPolicy' => [
                        'title' => $privacyPolicyTitle,
                        'text' => str_replace('#APP_NAME', $application->getName(), $privacyPolicy),
                        'gdpr' => $application->getPrivacyPolicyGdpr(),
                    ],
                    'gdpr' => [
                        'isEnabled' => isGdpr(),
                    ],
                    'useHomepageBackground' => (boolean) $application->getUseHomepageBackgroundImageInSubpages(),
                    'backButton' => (string) $application->getBackButton(),
                    'myAccount' => $myAccount,
                ],
                'homepageImage' => $homepageImageB64
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

        // Time to generate the current block!
        $loadBlock['x-delay'] = microtime(true) - $blockStart;

        return $loadBlock;
    }

    /**
     * @param $application
     * @param $currentLanguage
     * @param $request
     * @return array|false|string
     * @throws Zend_Exception
     */
    public function _featureBlock($application, $currentLanguage, $request)
    {
        $linkCodes = ['weblink_mono', 'prestashop', 'magento', 'volusion', 'woocommerce', 'shopify'];
        $appVersion = $request->getBodyParams()["version"];
        $appId = $application->getId();
        $appKey = $application->getKey();
        $cacheId = 'v4_front_mobile_home_findall_app_' . $appId . '_locale_' . $currentLanguage;
        $blockStart = microtime(true);
        if (!$result = $this->cache->load($cacheId)) {
            /**
             * @var $optionValues Application_Model_Option_Value[]
             */
            $optionValues = $application->getPages(10, true);
            $featureBlock = [];
            $color = $application->getBlock('tabbar')->getImageColor();
            $backgroundColor = $application->getBlock('tabbar')->getBackgroundColor();

            $touchedValues = [];
            $myAccountIgnore = false;

            foreach ($optionValues as $optionValue) {
                // We will ignore next tabbar_accounts iterations (ie: duplicates)
                if ($optionValue->getCode() === "tabbar_account") {
                    if ($myAccountIgnore === true) {
                        continue;
                    }
                    $myAccountIgnore = true;
                }

                $touchedValues[$optionValue->getId()] = [
                    'touched_at' => (integer)$optionValue->getTouchedAt(),
                    'expires_at' => (integer)$optionValue->getExpiresAt()
                ];

                try {
                    $object = $optionValue->getObject();

                    // In-App-Browser / Browser options!
                    $hideNavbar = null;
                    $useExternalApp = null;

                    if (count($optionValues) >= 50) {
                        if (in_array($optionValue->getCode(), ['folder', 'folder_v2', 'custom_page'])) {
                            $embedPayload = false;
                        } else {
                            $embedPayload = $optionValue->getEmbedPayload($request);
                        }
                    } else {
                        $embedPayload = $optionValue->getEmbedPayload($request);
                    }

                    try {
                        $settings = Json::decode($optionValue->getSettings());
                    } catch (\Exception $e) {
                        $settings = [];
                    }

                    // Special uri places
                    $uris = $optionValue->getAppInitUris();

                    // End link special code!
                    $blockData = [
                        'value_id' => (integer) $optionValue->getId(),
                        'id' => (integer) $optionValue->getId(),
                        'layout_id' => (integer) $optionValue->getLayoutId(),
                        'code' => $optionValue->getCode(),
                        'name' => $optionValue->getTabbarName(),
                        'subtitle' => $optionValue->getTabbarSubtitle(),
                        'is_active' => (boolean) $optionValue->isActive(),
                        'is_visible' => (boolean) $optionValue->getIsVisible(),
                        'url' => $uris['featureUrl'],
                        'hide_navbar' => (boolean) $hideNavbar,
                        'use_external_app' => (boolean) $useExternalApp,
                        'path' => $uris['featurePath'],
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
                        'homepage' => ($optionValue->getFolderCategoryId() === null),
                        'settings' => $settings,
                        'touched_at' => (integer) $optionValue->getTouchedAt(),
                        'expires_at' => (integer) $optionValue->getExpiresAt()
                    ];

                    // 4.18.3 link special options!
                    if ($object->getLink() &&
                        in_array($blockData['code'], $linkCodes, false)) {

                        $objectLink = $object->getLink();

                        // pre 4.18.3
                        $blockData['hide_navbar'] = $objectLink->getHideNavbar();
                        $blockData['use_external_app'] = $objectLink->getUseExternalApp();

                        // post 4.18.3 options
                        $blockData['link_url'] = (string) $objectLink->getData('url');
                        $blockData['external_browser'] = (boolean) $objectLink->getExternalBrowser();
                        $blockData['options'] = $objectLink->getOptions();
                    }

                    $featureBlock[] = $blockData;
                    /**
                     *
                     */
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
                'is_active' => (boolean) $option->isActive(),
                'is_visible' => (boolean) $application->usesUserAccount(),
                'url' => $this->getUrl('customer/mobile_account_login'),
                'path' => $this->getPath('customer/mobile_account_login'),
                'login_url' => $this->getUrl('customer/mobile_account_login'),
                'login_path' => $this->getPath('customer/mobile_account_login'),
                'edit_url' => $this->getUrl('customer/mobile_account_edit'),
                'edit_path' => $this->getPath('customer/mobile_account_edit'),
                'icon_url' => $this->getRequest()->getBaseUrl() . $this->_getColorizedImage($option->getIconUrl(), $accountColor),
                'icon_is_colorable' => (boolean)$accountColorizable
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

        // Dynamic patches (non-cached) for specific app versions
        if (version_compare($appVersion, '4.15.6', '<')) {
            // Apply patches.

            # 1. Places
            foreach ($dataHomepage['pages'] as &$page) {
                if ($page['code'] === 'places') {
                    $page['path'] = sprintf('/%s/places/mobile_list/index/value_id/%s',
                        $appKey,
                        $page['value_id']);
                }
            }
            unset($page);
        }

        if (version_compare($appVersion, '4.16.0', '<')) {
            // Apply patches.

            # 2. My account
            $fixedPages = [];
            foreach ($dataHomepage['pages'] as &$page) {
                if ($page['code'] !== 'tabbar_account') {
                    $fixedPages[] = $page;
                }
            }
            unset($page);
            $dataHomepage['pages'] = $fixedPages;
        }

        // Dynamic patches (non-cached) for specific app versions!
        if (version_compare($appVersion, '4.18.1', '<')) {
            # 3. M-Commerce
            foreach ($dataHomepage['pages'] as &$page) {
                if ($page['code'] === 'm_commerce') {
                    $page['path'] = sprintf('/%s/mcommerce/mobile_category/index/value_id/%s',
                        $appKey,
                        $page['value_id']);
                }
            }
            unset($page);
        }
        // Dynamic patches (non-cached) for specific app versions!

        // Don't cache customer information!
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

    /**
     * @param $application
     * @param $currentLanguage
     * @return array|false|mixed|string
     */
    public function _translationBlock ($application, $currentLanguage)
    {
        // Cache is based on locale + appId.
        $appId = $application->getId();
        $cacheId = 'v4_application_mobile_translation_findall_app_' . $appId . '_locale_' . $currentLanguage;
        $blockStart = microtime(true);
        if (!$result = $this->cache->load($cacheId)) {
            Siberian_Cache_Translation::init();
            $translationBlock = Core_Model_Translator::getTranslationsFor('ionic');

            if (empty($translationBlock)) {
                $translationBlock = ['_empty-translation-cache_' => true];
            }
    
            $this->cache->save($translationBlock, $cacheId, [
                'v4',
                'mobile_translation',
                'mobile_translation_locale_' . $currentLanguage
            ]);

            $translationBlock['x-cache'] = 'MISS';
        } else {

            $translationBlock = $result;
            $translationBlock['x-cache'] = 'HIT';
        }
        $translationBlock['_locale'] = strtolower(str_replace('_', '-', $currentLanguage));

        // Time to generate the current block!
        $translationBlock['x-delay'] = microtime(true) - $blockStart;
        
        return $translationBlock;
    }

    /**
     * @param $application
     * @param $loadBlock
     * @return mixed
     * @throws Zend_Session_Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function _customerBlock ($application, $loadBlock)
    {
        $session = $this->getSession();
        $customer = $session->getCustomer();
        $customerId = $customer->getCustomerId();
        $isLoggedIn = false;

        // Facebook token refresh for Facebook Login!
        $this->_refreshFacebookUserToken($customer);

        $loadBlock['customer'] = [
            'id' => (integer) $customerId,
            'can_connect_with_facebook' => (boolean) $application->getFacebookId(),
            'can_access_locked_features' => (boolean) ($customerId && $session->getCustomer()->canAccessLockedFeatures()),
            'token' => Zend_Session::getId()
        ];

        if ($customerId) {
            $metadata = $customer->getMetadatas();
            if (empty($metadata)) {
                $metadata = json_decode('{}'); // We really need a javascript object here
            }

            // Hide stripe customer id for secure purpose!
            if($metadata->stripe &&
                array_key_exists('customerId', $metadata->stripe) &&
                $metadata->stripe['customerId']) {
                unset($metadata->stripe['customerId']);
            }

            $isLoggedIn = true;

            $loadBlock['customer'] = array_merge($loadBlock['customer'], [
                'civility' => $customer->getCivility(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'nickname' => $customer->getNickname(),
                'image' => $customer->getImage(),
                'email' => $customer->getEmail(),
                'show_in_social_gaming' => (boolean) $customer->getShowInSocialGaming(),
                'is_custom_image' => (boolean) $customer->getIsCustomImage(),
                'metadatas' => $metadata,
                'can_connect_with_facebook' => (boolean) $application->getFacebookId(),
                'can_access_locked_features' =>
                    (boolean) ($customerId && $customer->canAccessLockedFeatures()),
                "extendedFields" => Account::getFields([
                    "application" => $application,
                    "request" => $this->getRequest(),
                    "session" => $session,
                ]),
            ]);

            if (Siberian_CustomerInformation::isRegistered('stripe')) {
                $exporterClass = Siberian_CustomerInformation::getClass('stripe');
                if (class_exists($exporterClass) &&
                    method_exists($exporterClass, 'getInformation')) {
                    $transitionalObject = new $exporterClass();
                    $info = $transitionalObject->getInformation($customer->getId());
                    $data['stripe'] = $info ? $info : [];
                }
            }
        }

        $loadBlock['customer'] = array_merge($loadBlock['customer'], [
            'isLoggedIn' => $isLoggedIn,
            'is_logged_in' => $isLoggedIn
        ]);

        return $loadBlock;
    }

    /**
     * @param $application
     * @param $request
     * @param bool $refresh
     * @return array
     */
    public function _manifestBlock($application, $request, $refresh = false)
    {
        $appId = $application->getId();
        $appIcon = $application->getIcon();
        $baseUrl = $request->getBaseUrl();
        $startupImage = $application->getStartupImageUrl();

        $manifestMameBase = Core_Model_Directory::getTmpDirectory(true) . '/webapp_manifest_' . $appId . '.json';
        $manifestName = Core_Model_Directory::getTmpDirectory() . '/webapp_manifest_' . $appId . '.json';

        $appIconBase64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($appIcon))
            ->scaleResize(192, 192);
        $appIcon144Base64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($appIcon))
            ->scaleResize(144, 144);
        $appIcon512Base64 = Siberian_Image::open(Core_Model_Directory::getBasePathTo($appIcon))
            ->scaleResize(512, 512);
        $startupImageBase64 = $baseUrl . '/' . $startupImage;

        $appIconBase64 = str_replace(Core_Model_Directory::getBasePathTo(''),
            $baseUrl . '/', $appIconBase64->png());
        $appIcon144Base64 = str_replace(Core_Model_Directory::getBasePathTo(''),
            $baseUrl . '/', $appIcon144Base64->png());
        $appIcon512Base64 = str_replace(Core_Model_Directory::getBasePathTo(''),
            $baseUrl . '/', $appIcon512Base64->png());

        $blocks = $application->getBlocks();
        $themeColor = null;
        $generalColor = null;
        foreach ($blocks as $block) {
            if ($block->getBackgroundColorVariableName() === '$bar-custom-bg') {
                $themeColor = $block;
            }

            if ($block->getBackgroundColorVariableName() === '$general-custom-bg') {
                $generalColor = $block;
            }
        }

        if (!is_readable($manifestMameBase) || $refresh) {
            // Generate manifest!
            $manifest = [
                'name' => $application->getName(),
                'short_name' => cut($application->getName(), 12, ''),
                'start_url' => '/var/apps/browser/index-prod.html#/' . $application->getKey(),
                'display' => 'fullscreen',
                'icons' => [
                    [
                        'src' => $appIcon144Base64,
                        'sizes' => '144x144',
                        'type' => 'image/png',
                        'density' => 4.0,
                    ],
                    [
                        'src' => $appIconBase64,
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'density' => 4.0,
                    ],
                    [
                        'src' => $appIcon512Base64,
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'density' => 4.0,
                    ]
                ],
                'theme_color' => $themeColor->getBackgroundColor(),
                'background_color' => $generalColor->getBackgroundColor()
            ];

            File::putContents($manifestMameBase, Siberian_Json::encode($manifest));
        }

        //Collect images and manifest url!
        $manifestBlock = [
            'startupImageUrl' => $startupImageBase64,
            'iconUrl' => $appIconBase64,
            'manifestUrl' => $manifestName,
            'themeColor' => $themeColor->getBackgroundColor(),
        ];

        return $manifestBlock;
    }

    /**
     *
     * $application: {
     *  use_ads > application ads
     *  owner_use_ads > backoffice specific ads
     *  system_config > default platform ads
     * }
     *
     * @param $application
     * @return array
     */
    public function _admobSettings($application)
    {
        $payload = [
            'ios_weight' => [
                'app' => 1,
                'platform' => 0,
            ],
            'android_weight' => [
                'app' => 1,
                'platform' => 0,
            ],
            'app' => [
                'ios' => [
                    'banner_id' => false,
                    'interstitial_id' => false,
                    'banner' => false,
                    'interstitial' => false,
                    'videos' => false,
                ],
                'android' => [
                    'banner_id' => false,
                    'interstitial_id' => false,
                    'banner' => false,
                    'interstitial' => false,
                    'videos' => false,
                ],
            ],
            'platform' => [
                'ios' => [
                    'banner_id' => false,
                    'interstitial_id' => false,
                    'banner' => false,
                    'interstitial' => false,
                    'videos' => false,
                ],
                'android' => [
                    'banner_id' => false,
                    'interstitial_id' => false,
                    'banner' => false,
                    'interstitial' => false,
                    'videos' => false,
                ],
            ]
        ];

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

            $ios_types = explode('-', $ios_device->getOwnerAdmobType());
            $ios_weight = (integer) $ios_device->getOwnerAdmobWeight();
            $android_types = explode('-', $android_device->getOwnerAdmobType());
            $android_weight = (integer) $android_device->getOwnerAdmobWeight();

            $payload['platform'] = [
                'ios' => [
                    'banner_id' => $ios_device->getOwnerAdmobId(),
                    'interstitial_id' => $ios_device->getOwnerAdmobInterstitialId(),
                    'banner' => (boolean) in_array('banner', $ios_types),
                    'interstitial' => (boolean) in_array('interstitial', $ios_types),
                    'videos' => (boolean) in_array('videos', $ios_types), # Prepping the future.
                ],
                'android' => [
                    'banner_id' => $android_device->getOwnerAdmobId(),
                    'interstitial_id' => $android_device->getOwnerAdmobInterstitialId(),
                    'banner' => (boolean) in_array('banner', $android_types),
                    'interstitial' => (boolean) in_array('interstitial', $android_types),
                    'videos' => (boolean) in_array('videos', $android_types), # Prepping the future.
                ],
            ];

            if (($ios_weight >= 0) && ($ios_weight <= 100)) {
                $weight = ($ios_weight/100);
                $payload['ios_weight']['platform'] = $weight;
                $payload['ios_weight']['app'] = (1 - $weight);
            }

            if (($android_weight >= 0) && ($android_weight <= 100)) {
                $weight = ($android_weight/100);
                $payload['android_weight']['platform'] = $weight;
                $payload['android_weight']['app'] = (1 - $weight);
            }

        } else if (($planUseAds || System_Model_Config::getValueFor('application_owner_use_ads'))) {

            $ios_key = 'application_' . $ios_device->getType()->getOsName() . '_owner_admob_%s';
            $android_key = 'application_' . $android_device->getType()->getOsName() . '_owner_admob_%s';

            $ios_types = explode('-', System_Model_Config::getValueFor(sprintf($ios_key, 'type')));
            $ios_weight = (integer) System_Model_Config::getValueFor(sprintf($ios_key, 'weight'));
            $android_types = explode('-', System_Model_Config::getValueFor(sprintf($android_key, 'type')));
            $android_weight = (integer) System_Model_Config::getValueFor(sprintf($android_key, 'weight'));

            $payload['platform'] = [
                'ios' => [
                    'banner_id' => System_Model_Config::getValueFor(sprintf($ios_key, 'id')),
                    'interstitial_id' => System_Model_Config::getValueFor(sprintf($ios_key, 'interstitial_id')),
                    'banner' => (boolean) in_array('banner', $ios_types),
                    'interstitial' => (boolean) in_array('interstitial', $ios_types),
                    'videos' => (boolean) in_array('videos', $ios_types), # Prepping the future.
                ],
                'android' => [
                    'banner_id' => System_Model_Config::getValueFor(sprintf($android_key, 'id')),
                    'interstitial_id' => System_Model_Config::getValueFor(sprintf($android_key, 'interstitial_id')),
                    'banner' => (boolean) in_array('banner', $android_types),
                    'interstitial' => (boolean) in_array('interstitial', $android_types),
                    'videos' => (boolean) in_array('videos', $android_types), # Prepping the future.
                ],
            ];

            if (($ios_weight >= 0) && ($ios_weight <= 100)) {
                $weight = ($ios_weight/100);
                $payload['ios_weight']['platform'] = $weight;
                $payload['ios_weight']['app'] = (1 - $weight);
            }

            if (($android_weight >= 0) && ($android_weight <= 100)) {
                $weight = ($android_weight/100);
                $payload['android_weight']['platform'] = $weight;
                $payload['android_weight']['app'] = (1 - $weight);
            }
        }

        if ($application->getUseAds()) {

            $ios_types = explode('-', $ios_device->getAdmobType());
            $android_types = explode('-', $android_device->getAdmobType());

            $payload['app'] = [
                'ios' => [
                    'banner_id' => $ios_device->getAdmobId(),
                    'interstitial_id' => $ios_device->getAdmobInterstitialId(),
                    'banner' => (boolean) in_array('banner', $ios_types),
                    'interstitial' => (boolean) in_array('interstitial', $ios_types),
                    'videos' => (boolean) in_array('videos', $ios_types), # Prepping the future.
                ],
                'android' => [
                    'banner_id' => $android_device->getAdmobId(),
                    'interstitial_id' => $android_device->getAdmobInterstitialId(),
                    'banner' => (boolean) in_array('banner', $android_types),
                    'interstitial' => (boolean) in_array('interstitial', $android_types),
                    'videos' => (boolean) in_array('videos', $android_types), # Prepping the future.
                ],
            ];
        } else {
            // If user don't use admob, split revenue is 100% for platform!
            $payload['ios_weight']['platform'] = 1;
            $payload['ios_weight']['app'] = 0;
            $payload['android_weight']['platform'] = 1;
            $payload['android_weight']['app'] = 0;
        }

        return $payload;
    }

    /**
     * Single action to refresh the user Facebook Login token!
     *
     * @param $customer
     */
    public function _refreshFacebookUserToken ($customer)
    {
        $customerFacebookDatas = $customer->getSocialDatas('facebook');

        if (!empty($customerFacebookDatas) &&
            isset($customerFacebookDatas['datas'])) {
            $socialDatas = unserialize($customerFacebookDatas['datas']);
            if (isset($socialDatas['access_token'])) {
                $accessToken = Core_Model_Lib_Facebook::getOrRefreshToken($socialDatas['access_token']);

                $social_datas['access_token'] = $accessToken;
                $customerFacebookDatas['datas'] = $socialDatas;
                $customerFacebookDatas['id'] = $customerFacebookDatas['social_id'];
                $customer->setSocialData('facebook', $customerFacebookDatas);
                $customer->save();
            }
        }
    }
}
