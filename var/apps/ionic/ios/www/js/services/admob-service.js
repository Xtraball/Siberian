/*global
 App, ionic, _
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
App.service("AdmobService", function ($rootScope, $window) {

    var service = {
        interstitialWeights: {
            start: {
                "show": 0.333,
                "skip": 0.667
            },
            low: {
                "show": 0.025,
                "skip": 0.975
            },
            default: {
                "show": 0.06,
                "skip": 0.94
            },
            medium: {
                "show": 0.125,
                "skip": 0.875
            }
        },
        interstitialState: "start",
        viewEnterCount: 0
    };

    service.get_weight = function(probs) {
        var random = _.random(0, 1000);
        var offset = 0;
        var key_used = "app";
        var match = false;
        _.forEach(probs, function(value, key) {
            offset += (value * 1000);
            if(!match && (random <= offset)) {
                key_used = key;
                match = true;
            }
        });
        return key_used;
    };

    service.init = function(options) {

        if ($rootScope.isNativeApp && $window.AdMob) {

            var whom = "app";
            var _options = {};
            if(ionic.Platform.isIOS()) {
                whom = service.get_weight(options.ios_weight);
                _options = options[whom].ios;
                service.initWithOptions(_options);
            }

            if(ionic.Platform.isAndroid()) {
                whom = service.get_weight(options.android_weight);
                _options = options[whom].android;
                service.initWithOptions(_options);
            }

        }
    };

    service.initWithOptions = function(options) {

        if(options.banner) {

            $window.AdMob.createBanner({
                adId:       options.banner_id,
                adSize:     "SMART_BANNER",
                position:   $window.AdMob.AD_POSITION.BOTTOM_CENTER,
                autoShow:   true
            });

        }

        if(options.interstitial) {

            $window.AdMob.prepareInterstitial({
                adId:       options.insterstitial_id,
                autoShow:   false
            });

            $rootScope.$on("$ionicView.enter", function () {

                service.viewEnterCount += 1;

                /** After 12 views, increase chances to show an Interstitial ad */
                if(service.viewEnterCount >= 12) {
                    service.interstitialState = "medium";
                }

                var action = service.get_weight(service.interstitialWeights[service.interstitialState]);
                if(action === "show") {
                    $window.AdMob.showInterstitial();

                    /** Then prepare the next one. */
                    $window.AdMob.prepareInterstitial({
                        adId:       options.insterstitial_id,
                        autoShow:   false
                    });

                    if(service.interstitialState === "start") {
                        service.interstitialState = "low";
                    } else {
                        service.interstitialState = "default";
                    }

                    service.viewEnterCount = 0;
                }
            });
        }

    };

    return service;
});
