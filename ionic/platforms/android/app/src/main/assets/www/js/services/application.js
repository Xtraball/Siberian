/**
 * Application
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.20.9
 */
angular
    .module('starter')
    .service('Application', function ($pwaRequest, $q, $rootScope, $session, $timeout, $ionicPlatform, $window) {
    var service = {
        /** @deprecated, should used DEVICE_TYPE with constants */
        is_webview: !IS_NATIVE_APP,
        known_modules: {
            // "booking"                   : "Booking", Removed not used anymore.
            'calendar': 'Event',
            'catalog': 'Catalog',
            // "code_scan"                 : "null",
            // "contact"                   : "Contact", Removed not used anymore.
            'custom_page': 'Cms',
            'discount': 'Discount',
            // "facebook"                  : "null",
            'fanwall': 'Newswall',
            // "folder"                    : "Folder", Removed not used anymore.
            'form': 'Form',
            // "image_gallery"             : "Image", Removed not used anymore.
            // "inapp_messages"            : "null",
            // "loyalty"                   : "LoyaltyCard",Removed not used anymore.
            // "m_commerce"                : "null",
            // "magento"                   : "null", weblink_mono, not required
            // "maps"                      : "null", Removed not used anymore.
            'music_gallery': 'MusicPlaylist',
            'newswall': 'Newswall',
            // "padlock"                   : "null",
            'places': 'Places',
            // "prestashop"                : "null",  weblink_mono, not required
            // "privacy_policy"            : "null", already loaded in loadv2
            //'push_notification': 'Push',
            //'qr_discount': 'Push',
            // "radio"                     : "Radio",Removed not used anymore.
            'rss_feed': 'Rss',
            'set_meal': 'SetMeal',
            // "shopify"                   : "null", weblink_mono, not required
            'social_gaming': 'SocialGaming',
            // "source_code"               : "SourceCode",Removed not used anymore.
            // "tip"                       : "Tip",Removed not used anymore.
            // "topic"                     : "Topic",Removed not used anymore.
            'twitter': 'Twitter',
            'video_gallery': 'Videos',
            // "volusion"                  : "null", weblink_mono, not required
            // "weather"                   : "Weather",Removed not used anymore.
            // "weblink_mono"              : "null", weblink_mono, not required
            // "weblink_multi"             : "Links", Removed not used anymore.
            // "woocommerce"               : "null", weblink_mono, not required
            'wordpress': 'Wordpress'
        },
        lazyLoadCodes: {
            'calendar': ['event'],
            'custom_page': ['cms'],
            'fanwall': ['newswall'],
            'music_gallery': ['media'],
            'places': ['cms', 'places'],
            'qr_discount': ['discount'],
            'rss_feed': ['rss'],
            'set_meal': ['catalog'],
            'video_gallery': ['video'],
            //'push_notification': ['push']
        }
    };

    var _loaded = false;
    var _loaded_resolver = $q.defer();
    var _ready = false;
    var _ready_resolver = $q.defer();

    /**
     * We are about to pre-load current features.
     *
     * @param pages
     */
    service.preLoad = function (pages) {
        // Disabled until 5.0 or further update
        //return;
    };

    Object.defineProperty(service, 'loaded', {
        get: function () {
            if (_loaded) {
                return $q.resolve();
            }
            return _loaded_resolver.promise;
        },
        set: function (value) {
            _loaded = !!value;
            if (_loaded === true) {
                _loaded_resolver.resolve();
            }
        }
    });

    Object.defineProperty(service, 'ready', {
        get: function () {
            if (_ready) {
                return $q.resolve();
            }
            return _ready_resolver.promise;
        },
        set: function (value) {
            _ready = !!value;
            if (_ready === true) {
                _ready_resolver.resolve();
            }
        }
    });

    service.app_id = null;
    service.app_name = null;
    service.googlemaps_key = null;

    /** @todo change this ... */
    service.is_customizing_colors = ($window.location.href.indexOf('application/mobile_customization_colors/') >= 0);

    /**
     * Populate Application service on load
     *
     * @param data
     */
    service.populate = function (data) {
        service.application = data.application;
        service.app_id = data.application.id;
        service.app_name = data.application.name;
        service.privacyPolicy = data.application.privacyPolicy;
        service.gdpr = data.application.gdpr;
        service.googlemaps_key = data.application.gmapsKey;
        service.is_locked = data.application.is_locked;
        service.homepage_background = data.application.useHomepageBackground;
        service.backButton = data.application.backButton;
        service.backButtonClass = data.application.backButtonClass;
        service.leftToggleClass = data.application.leftToggleClass;
        service.rightToggleClass = data.application.rightToggleClass;
        service.myAccount = data.application.myAccount;
        service.ipinfo_key = data.application.ipinfo_key;

        // Small base64 default image, while loading the real deal!
        service.default_background = data.homepageImage;
        service.colors = data.application.colors;

        service.ready = true;
    };

    service.reloadLocale = function (language) {
        return $pwaRequest.post('front/app/translations', {
            data: {
                user_language: language,
            },
            timeout: 30000,
            refresh: true
        });
    };

    /**
     *
     * @returns {string}
     */
    service.getBackIcon = function () {
        if (service.backButtonClass !== null) {
            return service.backButtonClass;
        } else if (service.backButton !== undefined) {
            switch (service.backButton) {
                case 'ion-android-arrow-back':
                case 'ion-arrow-left-a':
                case 'ion-arrow-left-b':
                case 'ion-arrow-left-c':
                case 'ion-arrow-return-left':
                case 'ion-chevron-left':
                case 'ion-home':
                case 'ion-ios-arrow-back':
                case 'ion-ios-arrow-left':
                case 'ion-ios-arrow-thin-left':
                case 'ion-ios-home-outline':
                case 'ion-ios-home':
                case 'ion-ios-undo-outline':
                case 'ion-ios-undo':
                case 'ion-reply':
                    return 'icon ' + service.backButton;
                default:
                    return 'icon ion-ios-arrow-back';
            }
        }
        return 'icon ion-ios-arrow-back';
    };

    service.getLeftToggleIcon = function () {
        return (service.leftToggleClass !== null) ?
            service.leftToggleClas : 'icon ion-navicon-round';
    };

    service.getRightToggleIcon = function () {
        return (service.rightToggleClass !== null) ?
            service.rightToggleClass : 'icon ion-navicon-round';
    };

    return service;
});
