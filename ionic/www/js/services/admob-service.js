/*global
 angular, ionic, _
 */

/**
 * options: {
    "ios_weight": {
        "app": 0.58,
        "platform": 0.42
    },
    "android_weight": {
        "app": 0.23,
        "platform": 0.77
    },
    "app": {
        "ios": {
            "banner_id": "app-ios-banner",
            "interstitial_id": "app-ios-inter",
            "banner": false,
            "interstitial": true,
            "videos": false
        },
        "android": {
            "banner_id": "app-android-banner",
            "interstitial_id": "app-android-inter",
            "banner": true,
            "interstitial": false,
            "videos": false
        }
    },
    "platform": {
        "ios": {
            "banner_id": "owner-ios-banner",
            "interstitial_id": "owner-ios-inter",
            "banner": true,
            "interstitial": false,
            "videos": false
        },
        "android": {
            "banner_id": "owner-android-banner",
            "interstitial_id": "owner-android-inter",
            "banner": true,
            "interstitial": false,
            "videos": false
        }
    }
}
 */
angular.module('starter').service('AdmobService', function ($log, $rootScope, $window) {
    var service = {
        interstitialWeights: {
            start: {
                'show': 0.333,
                'skip': 0.667
            },
            low: {
                'show': 0.025,
                'skip': 0.975
            },
            default: {
                'show': 0.06,
                'skip': 0.94
            },
            medium: {
                'show': 0.125,
                'skip': 0.875
            }
        },
        interstitialState: 'start',
        viewEnterCount: 0,
        options: {},
        forbiddenStates: []
    };

    service.getWeight = function (probs) {
        var random = _.random(0, 1000);
        var offset = 0;
        var keyUsed = 'app';
        var match = false;
        _.forEach(probs, function (value, key) {
            offset = offset + (value * 1000);
            if (!match && (random <= offset)) {
                keyUsed = key;
                match = true;
            }
        });
        $log.debug('AdMob key used: ', keyUsed);
        return keyUsed;
    };

    service.init = function (options) {
        if ($rootScope.isNativeApp && $window.AdMob) {
            var whom = 'app';
            if (ionic.Platform.isIOS()) {
                $log.debug('AdMob init iOS');
                whom = service.getWeight(options.ios_weight);
                service.options = options[whom].ios;
                service.initWithOptions();
            }

            if (ionic.Platform.isAndroid()) {
                $log.debug('AdMob init Android');
                whom = service.getWeight(options.android_weight);
                service.options = options[whom].android;
                service.initWithOptions();
            }
        }
    };

    /**
     *
     */
    service.initWithOptions = function () {
        service.loadBanner();
        service.prepareInterstitial();
    };

    /**
     * Clear / Reload banner!
     */
    service.loadBanner = function () {
        if (service.options.banner) {
            $log.info('init admob banner');
            $window.AdMob.removeBanner();
            $window.AdMob.createBanner({
                adId: service.options.banner_id,
                adSize: 'SMART_BANNER',
                position: $window.AdMob.AD_POSITION.BOTTOM_CENTER,
                autoShow: true
            });
        } else {
            $log.info('!ko init admob banner');
        }
    };

    /**
     *
     */
    service.removeBanner = function () {
        if (service.options.banner) {
            $window.AdMob.removeBanner();
        }
    };

    /**
     * Add states that must never load Ads!
     * @param states
     */
    service.forbidStates = function (states) {
        service.forbiddenStates = service.forbiddenStates.concat(states);
    };

    /**
     *
     */
    service.prepareInterstitial = function () {
        if (service.options.interstitial) {
            $log.info('init interstitial banner');
            $window.AdMob.prepareInterstitial({
                adId: service.options.interstitial_id,
                autoShow: false
            });

            $rootScope.$on('$ionicView.enter', function (event, data) {

                // Check for any forbidden stateName
                var canReloadBanner = false;
                if (service.forbiddenStates.indexOf(data.stateName) !== -1) {
                    service.removeBanner();
                    // Return
                    return;
                } else {
                    canReloadBanner = true;
                }

                service.viewEnterCount = service.viewEnterCount + 1;

                // After 12 views, increase chances to show an Interstitial ad!
                if (service.viewEnterCount >= 12) {
                    service.interstitialState = 'medium';
                }

                var action = service.getWeight(service.interstitialWeights[service.interstitialState]);
                if (action === 'show') {
                    document.addEventListener('onAdDismiss', service._reload);

                    $window.AdMob.showInterstitial();

                    /** Then prepare the next one. */
                    $window.AdMob.prepareInterstitial({
                        adId: service.options.interstitial_id,
                        autoShow: false
                    });

                    if (service.interstitialState === 'start') {
                        service.interstitialState = 'low';
                    } else {
                        service.interstitialState = 'default';
                    }

                    service.viewEnterCount = 0;
                } else if (canReloadBanner) {
                    service._reload();
                }
            });
        } else {
            $log.info('!ko init interstitial banner');
        }
    };

    /**
     *
     * @private
     */
    service._reload = function () {
        service.loadBanner();
        // Remove the event listener until next interstitial load!
        document.removeEventListener('onAdDismiss', service._reload);
    };

    return service;
});
