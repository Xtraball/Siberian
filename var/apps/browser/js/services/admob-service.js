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

    var service = {};

    service.init = function(options) {

        if ($rootScope.isNativeApp && $window.AdMob) {

            var get_weigth = function(probs) {
                var random = _.random(0, 1000);
                var offset = 0;
                _.forEach(probs, function(value, key) {
                    offset += (value * 1000);
                    if(random <= offset) {
                        return key;
                    }
                });
            };

            var whom = "app";
            var _options = {};
            if(ionic.Platform.isIOS()) {
                whom = get_weigth(options.ios_weight);
                _options = options[whom].ios;
                service.initWithOptions(_options);
            }

            if(ionic.Platform.isAndroid()) {
                whom = get_weigth(options.android_weight);
                _options = options[whom].android;
                service.initWithOptions(_options);
            }

        }
    };

    service.initWithOptions = function(options) {

        if(options.banner) {
            $window.AdMob.createBanner({
                adId:       options.banner_id,
                position:   $window.AdMob.AD_POSITION.BOTTOM_CENTER,
                isTesting:  true,
                autoShow:   true
            });
        }

        if(options.insterstitial) {
            $window.AdMob.prepareInterstitial({
                adId:       options.insterstitial_id,
                isTesting:  true,
                autoShow:   true
            });
        }

    };

    return service;
});
