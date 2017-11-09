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
        viewEnterCount: 0
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
            var _options = {};
            if (ionic.Platform.isIOS()) {
                $log.debug('AdMob init iOS');
                whom = service.getWeight(options.ios_weight);
                _options = options[whom].ios;
                service.initWithOptions(_options);
            }

            if (ionic.Platform.isAndroid()) {
                $log.debug('AdMob init Android');
                whom = service.getWeight(options.android_weight);
                _options = options[whom].android;
                service.initWithOptions(_options);
            }
        }
    };

    service.initWithOptions = function (options) {
        if (options.banner) {
            $window.AdMob.createBanner({
                adId: options.banner_id,
                adSize: 'SMART_BANNER',
                position: $window.AdMob.AD_POSITION.BOTTOM_CENTER,
                autoShow: true
            });
        }

        if (options.interstitial) {
            $window.AdMob.prepareInterstitial({
                adId: options.interstitial_id,
                autoShow: false
            });

            $rootScope.$on('$ionicView.enter', function () {
                service.viewEnterCount = service.viewEnterCount + 1;

                // After 12 views, increase chances to show an Interstitial ad!
                if (service.viewEnterCount >= 12) {
                    service.interstitialState = 'medium';
                }

                var action = service.getWeight(service.interstitialWeights[service.interstitialState]);
                if (action === 'show') {
                    $window.AdMob.showInterstitial();

                    /** Then prepare the next one. */
                    $window.AdMob.prepareInterstitial({
                        adId: options.interstitial_id,
                        autoShow: false
                    });

                    if (service.interstitialState === 'start') {
                        service.interstitialState = 'low';
                    } else {
                        service.interstitialState = 'default';
                    }

                    service.viewEnterCount = 0;
                }
            });
        }
    };

    return service;
});
;/* global
    App, device, cordova
 */
angular.module('starter').service('Analytics', function ($cordovaGeolocation, $pwaRequest, $q, $log, $rootScope,
                                                         Application, Url) {
    var service = {};

    service.data = {};

    service.storeInstallation = function () {
        if (!Application.is_webview) {
            var url = Url.get('analytics/mobile_store/installation');
            var params = {
                OS: device.platform,
                OSVersion: device.version,
                Device: device.platform,
                DeviceVersion: device.model,
                deviceUUID: device.uuid,
                latitude: null,
                longitude: null
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.storeOpening = function () {
        var deferred = $q.defer();

        if (!Application.is_webview && (typeof cordova !== 'undefined')) {
            var url = Url.get('analytics/mobile_store/opening');
            var params = {
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params).then(function (result) {
                    deferred.resolve(result);
                }).catch(function (error) {

                });
            }, function () {
                service.postData(url, params).then(function (result) {
                    deferred.resolve(result);
                }).catch(function (error) {

                });
            });
        }

        return deferred.promise;
    };

    service.storeClosing = function () {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/closing');

            if (typeof service.data.storeClosingId === 'undefined') {
                $log.debug('aborting /analytics/mobile_store/closing, no id.');
                return;
            }

            var params = {
                id: service.data.storeClosingId
            };

            service.postData(url, params);
        }
    };

    service.storePageOpening = function (page) {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/pageopening');
            var params = {
                featureId: page.value_id,
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.storeProductOpening = function (product) {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/productopening');
            var params = {
                productId: product.id,
                name: product.name,
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.storeProductSold = function (products) {
        if (!$rootScope.isOverview) {
            var url = Url.get('analytics/mobile_store/productsold');
            var params = {
                products: products,
                OS: cordova.device ? device.platform : 'Browser',
                OSVersion: cordova.device ? device.version : null,
                Device: cordova.device ? device.platform : 'Browser',
                DeviceVersion: cordova.device ? device.model : null,
                deviceUUID: cordova.device ? device.uuid : null,
                latitude: null,
                longitude: null,
                locale: CURRENT_LANGUAGE
            };

            $cordovaGeolocation.getCurrentPosition({
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }).then(function (position) {
                params.latitude = position.coords.latitude;
                params.longitude = position.coords.longitude;

                service.postData(url, params);
            }, function () {
                service.postData(url, params);
            });
        }
    };

    service.postData = function (url, params) {
        return $pwaRequest.post(url, {
            data: params,
            cache: false,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });
    };

    return service;
});
;/* global
    App, caches, cacheName, ionic, DOMAIN, _, window, localStorage, IS_NATIVE_APP
*/

/**
 * Application
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Application', function ($pwaRequest, $q, $rootScope, $session, $timeout, $ionicPlatform,
                                                           $window, $queue, $log, Dialog, ProgressbarService) {
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
            'push_notification': 'Push',
            'qr_discount': 'Push',
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
            'push_notification': ['push']
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
                $log.info('Application loaded, resolving promise');
                return $q.resolve();
            }
            return _loaded_resolver.promise;
        },
        set: function (value) {
            _loaded = !!value;
            if (_loaded === true) {
                $log.info('Application loaded, resolving promise');
                _loaded_resolver.resolve();
            }
        }
    });

    Object.defineProperty(service, 'ready', {
        get: function () {
            if (_ready) {
                $log.info('Application ready, resolving promise');
                return $q.resolve();
            }
            return _ready_resolver.promise;
        },
        set: function (value) {
            _ready = !!value;
            if (_ready === true) {
                $log.info('Application ready, resolving promise');
                _ready_resolver.resolve();
            }
        }
    });

    service.app_id = null;
    service.app_name = null;
    service.googlemaps_key = null;

    /** @todo change this ... */
    service.is_customizing_colors = ($window.location.href.indexOf('application/mobile_customization_colors/') >= 0);

    /** @todo change this ... */
    Object.defineProperty(service, 'acceptedOfflineMode', {
        get: function () {
            return ($window.localStorage.getItem('sb-offline-mode') === 'ok');
        }
    });

    /**
     * Populate Application service on load
     *
     * @param data
     */
    service.populate = function (data) {
        service.app_id = data.application.id;
        service.app_name = data.application.name;
        service.privacy_policy = data.application.privacy_policy;
        service.privacy_policy_title = data.application.privacy_policy_title;
        service.googlemaps_key = data.application.googlemaps_key;
        service.is_locked = data.application.is_locked;
        service.offline_content = data.application.offline_content;
        service.homepage_background = data.application.homepage_background;

        // Small base64 default image, while loading the real deal!
        service.default_background = data.homepage_image;
        service.colors = data.application.colors;

        service.ready = true;
    };

    service.showCacheDownloadModalOrUpdate = function () {
        // Lazy Load progressbar, then dooooo it!
        ProgressbarService.init()
            .then(function () {
                $rootScope.progressBarPercent = 0;

                var offlineResponse = $window.localStorage.getItem('sb-offline-mode');

                if (offlineResponse === 'ok') {
                    $log.debug('offline mode has been accepted, updating');
                    service.updateCache(false);
                } else if (offlineResponse === 'no') {
                    $log.debug('offline mode has been refused in the past, not updating');
                } else {
                    $log.debug('offline mode need to be asked');
                    var title = 'Offline content';
                    var message = 'Do you want to download all the contents now to access it when offline? If you do, we recommend you to use a WiFi connection.';
                    var buttons = ['Yes', 'No'];

                    Dialog.confirm(title, message, buttons, 'text-center').then(function (res) {
                        if (res) {
                            $window.localStorage.setItem('sb-offline-mode', 'ok');

                            $rootScope.openLoaderProgress();
                            ProgressbarService.createCircle('.ui-progress-view-circle');

                            service.updateCache(true);
                        } else {
                            $window.localStorage.setItem('sb-offline-mode', 'no');
                        }
                    });
                }
            });
    };

    var _updatingCache = false;

    var _replace_tokens = function (url) {
        return _.isString(url) ?
            url.replace('%DEVICE_UID%', $session.getDeviceUid()).replace('%CUSTOMER_ID%', $rootScope.customer_id) : 0;
    };

    service.updateCache = function (forceMain) {
        if (window.OfflineMode) {
            window.OfflineMode.setCanCache();
        }

        if (_updatingCache === true) {
            return;
        }

        var device_screen = $session.getDeviceScreen();

        $pwaRequest.get('application/mobile_data/findall', {
            data: {
                device_uid: $session.getDeviceUid(),
                device_width: device_screen.width,
                device_height: device_screen.height
            },
            cache: false,
            timeout: 30000
        }).then(function (data) {
            $log.debug('application/mobile_data/findall', data);

            var total = data.paths.length + data.assets.length;
            if (isNaN(total)) {
                total = 100;
            }

            var progress = 0;
            var assets_done = JSON.parse($window.localStorage.getItem('sb-offline-mode-assets'));
            if (!_.isArray(assets_done)) {
                assets_done = [];
            }

            var fileQueue = [];
            var retryQueue = [];

            var delay = 500;
            var maxRequest = 15;
            if (ionic.Platform.isIOS()) {
                delay = 250;
                maxRequest = 3;
            }

            var requestCount = 0;
            var pathQueue = null;

            var updateFailed = function (asset) {
                requestCount = requestCount - 1;
                if (requestCount < 0) {
                    requestCount = 0;
                }

                // Restart queue
                if (pathQueue.paused && (requestCount <= maxRequest)) {
                    $log.debug('Start ' + requestCount);
                    pathQueue.start();
                }

                retryQueue.push(asset);
            };

            var updateProgress = function () {
                progress = progress + 1;

                requestCount = requestCount - 1;
                if (requestCount < 0) {
                    requestCount = 0;
                }

                // Restart queue!
                if (pathQueue.paused && (requestCount <= maxRequest)) {
                    $log.debug('Start ' + requestCount);
                    pathQueue.start();
                }

                if ($rootScope.isNativeApp) {
                    var percent = (progress / total);

                    // Change progress only if it's bigger. (don't go back ...)
                    if (percent.toFixed(2) > $rootScope.progressBarPercent) {
                        $rootScope.progressBarPercent = percent.toFixed(2);
                    }

                    if (isNaN($rootScope.progressBarPercent)) {
                        $rootScope.progressBarPercent = 0;
                    }

                    ProgressbarService.updateProgress($rootScope.progressBarPercent);
                    $window.localStorage.setItem('sb-offline-mode-assets', JSON.stringify(assets_done));

                    if ($rootScope.progressBarPercent >= 1) {
                        _updatingCache = false;

                        $timeout(function () {
                            ProgressbarService.remove();
                            $rootScope.closeLoaderProgress();
                        }, 1000);
                    }
                }
            };

            // Force end!
            var endProgress = function () {
                progress = total;
                $rootScope.progressBarPercent = 1;
                ProgressbarService.updateProgress($rootScope.progressBarPercent);
                $window.localStorage.setItem('sb-offline-mode-assets', JSON.stringify(assets_done));

                _updatingCache = false;

                $timeout(function () {
                    ProgressbarService.remove();
                    $rootScope.closeLoaderProgress();
                }, 1000);
            };

            // Check and add images not present in assets (useful for push which is device relative)
            var look_for_images = function (object) {
                _.forEach(object, function (obj, key) {
                    if (_.isString(obj) && (/\.(png|jpg|jpeg|gif)$/.test(obj))) {
                        var path = _replace_tokens(obj);

                        if (!/^https?:/.test(path)) {
                            path = (DOMAIN + '/' + path).replace(/([^:/])\/+/g, '$1/');
                        }

                        if (!_.includes(data.assets, path)) {
                            total = total + 1;
                            pathQueue.add({
                                type: 'asset',
                                path: path
                            });
                        }
                    } else if (_.isArray(obj) || _.isObject(obj)) {
                        look_for_images(obj);
                    }
                });
            };

            var retry = true;
            var fetchAssets = function (asset) {
                if (asset.type === 'path') {
                    requestCount = requestCount + 1;
                    $pwaRequest.get(asset.path, {
                        cache: !$rootScope.isOverview
                    }).then(function (data) {
                        if (_.isObject(data)) {
                            look_for_images(data);
                        }
                        updateProgress(asset);
                    }, function () {
                        if (retry) {
                            updateFailed(asset);
                        } else {
                            updateProgress();
                        }
                    });
                } else if (asset.type === 'asset') {
                    requestCount = requestCount + 1;

                    $pwaRequest.cacheImage(asset.path).then(function () {
                        updateProgress();
                        assets_done.push(asset.path);
                    }, function () {
                        if (retry) {
                            updateFailed(asset);
                        } else {
                            updateProgress();
                            assets_done.push(asset.path);
                        }
                    });
                }

                if (requestCount >= maxRequest) {
                    $log.debug('Paused ' + requestCount);
                    pathQueue.pause();
                }
            };

            var options = {
                delay: delay,
                paused: true,
                complete: function () {
                    $log.debug('Queue ends.');

                    updateProgress();
                    retry = false;
                    var _retryQueue = retryQueue;
                    if (retryQueue.length > 0) {
                        pathQueue = $queue.queue(fetchAssets, {
                            delay: 1000,
                            paused: true,
                            complete: function () {
                                $log.debug('Retry queue ends.');

                                updateProgress();
                                endProgress();
                            }
                        });
                        pathQueue.addEach(_retryQueue);
                        pathQueue.start();
                    } else {
                        endProgress();
                    }
                }
            };

            // Build objects
            _.forEach(data.paths, function (path) {
                fileQueue.push({
                    type: 'path',
                    path: _replace_tokens(path)
                });
            });

            _.forEach(data.assets, function (asset) {
                var path = _replace_tokens(asset);
                if (!_.includes(assets_done, path)) {
                    fileQueue.push({
                        type: 'asset',
                        path: path
                    });
                }
            });

            service.fileQueue = fileQueue;

            pathQueue = $queue.queue(fetchAssets, options);
            pathQueue.addEach(fileQueue);

            service.loaded.then(function () {
                if (forceMain) {
                    pathQueue.start();
                } else {
                    $ionicPlatform.on('pause', function (result) {
                        pathQueue.start();
                    });
                    $ionicPlatform.on('resume', function (result) {
                        pathQueue.pause();
                    });
                }
            });
        }, function () {
            _updatingCache = false;
        });
    };

    return service;
});
;angular.module('starter').service('ConnectionService', function ($ionicPlatform, $rootScope,
                                                                 $translate, $window, $log, $http, Dialog) {
    var service = {};

    var _isOnline = true;

    Object.defineProperty(service, 'isOnline', {
        get: function () {
            return _isOnline;
        }
    });

    Object.defineProperty(service, 'isOffline', {
        get: function () {
            return !service.isOnline;
        }
    });

    service.show_popup = null;

    var callbackFromNative = function (data) {
        if (service.isOnline === data.isOnline) {
            return;
        }

        _isOnline = data.isOnline;

        if (!service.show_popup && $rootScope.isNativeApp && !_isOnline) {
            service.show_popup = true;

            if (!$rootScope.onPause) {
                Dialog.alert($translate.instant('Info'),
                    $translate.instant('You have gone offline'),
                    $translate.instant('OK'), -1)
                    .then(function () {
                        service.show_popup = null;
                    });
            }
        }

        $rootScope.$broadcast('connectionStateChange', data);

        if (_isOnline) {
            $log.info('App is now online.');
            $window.StatusBar.backgroundColorByHexString('#000000');
        } else {
            $log.info('App is offline.');
            $window.StatusBar.backgroundColorByHexString('#d54c16');
        }
    };

    $ionicPlatform.ready(function () {
        if ($rootScope.isNativeApp && $window.OfflineMode) {
            $window.OfflineMode.setCheckConnectionURL(DOMAIN + '/ping.txt');
            $window.OfflineMode.registerCallback(callbackFromNative);
        }
    });

    return service;
});
;/*global
 angular
 */

angular.module("starter").service('ContextualMenu', function($ionicSideMenuDelegate, $timeout, HomepageLayout) {
    var DEFAULT_WIDTH = 275;

    var self = {};

    var _exists, _templateURL, _width, _is_enabled_function;

    Object.defineProperty(self, "exists", {
      get: function() { return _exists; }
    });

    Object.defineProperty(self, "templateURL", {
      get: function() { return _templateURL; }
    });

    Object.defineProperty(self, "width", {
      get: function() { return (angular.isNumber(_width) && _width > 0 && _width) || DEFAULT_WIDTH; }
    });

    Object.defineProperty(self, "isEnabled", {
      get: function() { return ((angular.isFunction(_is_enabled_function) && _is_enabled_function) || (function() { return self.exists; }))(); }
    });

    Object.defineProperty(self, "direction", {
        get: function() {
            return (HomepageLayout.properties.menu.position === "right") ? "left" : "right";
        }
    });

    self.reset = function() {
        $timeout(function() {
            _exists = false;
            _is_enabled_function = (function() { return self.exists; });
            _width = null;
            _templateURL = null;
        });
    };
    self.reset();

    self.set = function(templateURL, width, is_enabled_function) {
        if(angular.isString(templateURL) && templateURL.length > 0) {
            _exists = true;
            _templateURL = templateURL;
            _width = width;
            _is_enabled_function = is_enabled_function;
        } else {
            self.reset();
        }

        return (function() {
            if(_templateURL === templateURL) {
                self.reset();
            }
        });
    };

    self.toggle = function(open) {
        var direction = self.direction.slice(0, 1).toUpperCase()+self.direction.slice(1);

        if(!(open === true || open === false)) {
            open = !$ionicSideMenuDelegate["isOpen"+direction]();
        }

        if(self.exists && self.isEnabled) {
            $ionicSideMenuDelegate["toggle"+direction](open);
        }
    };

    self.open = function() {
        self.toggle(true);
    };

    self.close = function() {
        self.toggle(false);
    };


    return self;
});
;/*global
 angular
 */

/**
 * Country
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("Country", function($pwaRequest) {

    var service = {};

    service.findAll = function() {
        return $pwaRequest.get("/application/mobile_country/findall");
    };

    return service;
});;/*global
 angular, IS_NATIVE_APP
 */

/**
 * Dialog
 *
 * @author Xtraball SAS
 *
 * @note $cordovaDialogs has been removed in favor of $ionicPopup which is consistent over all devices,
 * and can be automatically dismissed
 */
angular.module('starter').service('Dialog', function ($ionicPopup, $timeout, $translate, $q) {
    var service = {
        is_open : false,
        stack   : []
    };

    /**
     * Un stack popups on event
     */
    service.unStack = function () {

        service.is_open = false;

        if (service.stack.length >= 1) {
            $timeout(function () {
                var dialog = service.stack.shift();

                switch(dialog.type) {
                    case 'alert':
                        service.renderAlert(dialog.data);
                        break;
                    case 'prompt':
                        service.renderPrompt(dialog.data);
                        break;
                    case 'confirm':
                        service.renderConfirm(dialog.data);
                        break;
                    case 'ionicPopup':
                        service.renderIonicPopup(dialog.data);
                        break;
                }
            }, 250);
        }
    };

    /**
     *
     * @param title
     * @param message
     * @param button
     * @param dismiss if -1 dismiss duration will be automatically calculated.
     * @returns {*}
     */
    service.alert = function (title, message, button, dismiss) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'alert',
            data: {
                title           : title,
                message         : message,
                button          : button,
                dismiss         : dismiss,
                promise         : deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;

    };

    /**
     * @param data
     */
    service.renderAlert = function (data) {
        service.is_open = true;

        var alertPromise = null;

        var message = $translate.instant(data.title);
        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        alertPromise = $ionicPopup
            .alert({
                title       : $translate.instant(data.title),
                template    : $translate.instant(data.message),
                cssClass    : cssClass,
                okText      : $translate.instant(data.button)
            });

        data.promise.resolve(alertPromise);

        alertPromise.then(function () {
            service.unStack();
        });

        if (typeof data.dismiss === 'number') {
            /**
             * -1 means automatic calculation
             */
            var duration = data.dismiss;
            if (data.dismiss === -1) {
                duration = Math.min(Math.max((message.length * 50), 2000), 7000) + 400;
            }

            $timeout(function () {
                alertPromise.close();
            }, duration);
        }
    };

    /**
     *
     * @param title
     * @param message
     * @param type
     * @param value
     */
    service.prompt = function (title, message, type, value) {
        var deferred = $q.defer();

        var localType = (type === undefined) ? 'text' : type;
        var localValue = (value === undefined) ? '' : value;

        /** Stack alert */
        service.stack.push({
            type: 'prompt',
            data: {
                title           : title,
                message         : message,
                type            : localType,
                value           : localValue,
                promise         : deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderPrompt = function (data) {
        service.is_open = true;

        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        return $ionicPopup
            .prompt({
                title               : $translate.instant(data.title),
                template            : $translate.instant(data.message),
                okText              : $translate.instant(data.button),
                cssClass            : cssClass,
                inputType           : data.type,
                inputPlaceholder    : $translate.instant(data.value)
            }).then(function (result) {
                if (result === undefined) {
                    data.promise.reject(result);
                } else {
                    data.promise.resolve(result);
                }

                service.unStack();
            });
    };

    /**
     * @param message
     * @param title
     * @param buttonsArray - ex: ['Ok', 'Cancel']
     * @param cssClass
     *
     * @returns Integer: 0 - no button, 1 - button 1, 2 - button 2
     */
    service.confirm = function (title, message, buttonsArray, cssClass) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'confirm',
            data: {
                title           : title,
                message         : message,
                buttons_array   : buttonsArray,
                css_class       : cssClass,
                promise         : deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     *
     * @return Promise
     */
    service.renderConfirm = function (data) {
        service.is_open = true;

        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        return $ionicPopup
            .confirm({
                title       : $translate.instant(data.title),
                cssClass    : data.css_class + ' ' + cssClass,
                template    : data.message,
                okText      : $translate.instant(data.buttons_array[0]),
                cancelText  : $translate.instant(data.buttons_array[1])
            }).then(function (result) {
                data.promise.resolve(result);
                service.unStack();
            });
    };

    /**
     * @param config
     *
     * @return Promise
     */
    service.ionicPopup = function (config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'ionicPopup',
            data: {
                config  : config,
                promise : deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     *
     * @return Promise
     */
    service.renderIonicPopup = function (data) {
        service.is_open = true;

        return $ionicPopup
            .show(data.config)
            .then(function (result) {
                data.promise.resolve(result);
                service.unStack();
            });
    };

    return service;
});

/** @deprecated, use Dialog instead, will be removed by mid-2017, SafePopups is a proxy to Dialog. */
angular.module('starter').service('SafePopups', function (Dialog) {
    var service = {};
    service.show = function (type, params) {
        var button = {};
        switch (type) {
            case 'alert':
                if (params.buttons.length === 1) {
                    button = params.buttons[0];
                }
                return Dialog.alert(params.title, params.template, button);
            case 'confirm':
                return Dialog.confirm(params.title, params.template, params.buttons, '');
            default:
                return Dialog.ionicPopup(params);
        }
    };
    return service;
});
;/**
 * FacebookConnect for users (login)
 */
angular.module('starter').service('FacebookConnect', function ($cordovaOauth, $rootScope, $timeout, $window,
                                                              Customer, Dialog, SB, Loader) {
    var _this = this;

    _this.app_id = null;
    _this.version = 'v2.7';
    _this.is_initialized = false;
    _this.is_logged_in = false;
    _this.access_token = null;
    _this.permissions = null;
    _this.fb_login = null;

    _this.login = function () {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
            var scope = (_this.permissions) ? _this.permissions.join(',') : '',
                redirectUri = encodeURIComponent(DOMAIN + '/' + APP_KEY + '?login_fb=true');

            $window.location = 'https://graph.facebook.com/oauth/authorize?client_id=' +
                _this.app_id+'&scope=' + scope + '&response_type=token&redirect_uri=' + redirectUri;
        } else {
            Loader.show();
            $cordovaOauth.facebook(_this.app_id, _this.permissions)
                .then(function (result) {
                    Customer.loginWithFacebook(result.access_token)
                        .then(function () {
                            Customer.login_modal.hide();
                        }).finally(function () {
                            Loader.hide();
                        });
                }, function (error) {
                    Dialog.alert('Login error', error, 'OK', -1)
                        .then(function () {
                            Customer.login_modal.hide();
                            Loader.hide();
                        });
                });
        }
    };

    _this.logout = function () {
        _this.is_logged_in = false;
        _this.access_token = null;
    };

    $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $timeout(function () {
            _this.logout();
        });
    });

    return _this;
});
;/*global
    google, App, angular
*/
angular.module("starter").service('GoogleMaps', function ($cordovaGeolocation, $location, $q, $rootScope, $translate, $window, Application) {
    "use strict";

    var __self = {
        is_loaded: false,
        reset: function() {

            if(service.directionsRenderer) {
                service.directionsRenderer.setPanel(null);
                service.directionsRenderer.setMap(null);
            }

            for(var i = 0; i < service.markers; i++) {
                google.maps.event.removeListener(service.markers[i], 'click');
                service.markers[i].setMap(null);
            }

            service.map = null;
            service.panel_id = null;
            service.directionsRenderer = null;
            service.markers = [];
            __self.is_loaded = false;
        },
        _calculateRoute: function(origin, destination, params, rejectWithResponseAndStatus) {

            var deferred = $q.defer();

            if(!_.isObject(params))
                params = {
                    mode: google.maps.DirectionsTravelMode.WALKING,
                    unitSystem: google.maps.UnitSystem.METRIC
                };

            if(!_.isObject(params.request))
                params.request = {};


            if (!this.directionsService) {
                this.directionsService = new google.maps.DirectionsService();
            }

            var request = _.merge({
                origin: new google.maps.LatLng(origin.latitude, origin.longitude),
                destination: new google.maps.LatLng(destination.latitude, destination.longitude),
                travelMode: params.mode,
                unitSystem: params.unitSystem
            }, params.request);

            this.directionsService.route(request, function (response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    deferred.resolve(response);
                } else {
                    var errorMessage = $translate.instant(
                        status === "ZERO_RESULTS" ?
                            "There is no route available with these informations." :
                            "An unexpected error occurred while calculating the route."
                    );
                    console.error(errorMessage, status);
                    if(rejectWithResponseAndStatus === true) {
                        deferred.reject([response, status]);
                    } else {
                        deferred.reject(errorMessage);
                    }
                }

            });

            return deferred.promise;
        },
        _updateBoundsFromPoints: function (bounds, p) {
            var latitude = parseFloat(p.latitude);
            var longitude = parseFloat(p.longitude);

            if (bounds[0][0] === null || bounds[0][0] > latitude) {
                bounds[0][0] = latitude;
            }

            if (bounds[1][0] === null || bounds[1][0] < latitude) {
                bounds[1][0] = latitude;
            }

            if (bounds[0][1] === null || bounds[0][1] > longitude) {
                bounds[0][1] = longitude;
            }

            if (bounds[1][1] === null || bounds[1][1] < longitude) {
                bounds[1][1] = longitude;
            }

            return bounds;
        },
        _extendBounds: function (bounds, margin) {
            if (margin) {
                var latitudeMargin = (bounds[1][0] - bounds[0][0]) * margin;
                if (latitudeMargin === 0) {
                    latitudeMargin = 0.02;
                }
                bounds[0][0] -= latitudeMargin;
                bounds[1][0] += latitudeMargin;

                var longitudeMargin = (bounds[1][1] - bounds[0][1]) * margin;
                if (longitudeMargin === 0) {
                    longitudeMargin = 0.01;
                }
                bounds[0][1] -= longitudeMargin;
                bounds[1][1] += longitudeMargin;
            }

            return bounds;
        }
    };

    var gmap_callbacks = [];
    var gmap_script_appended = false;
    var gmap_loaded = false;
    var _init_called = false;

    $window.initGMapCallback = function() {
        if(gmap_script_appended) {
            gmap_loaded = true;
            console.log("Gmap loaded, calling callbacks");
            while(gmap_callbacks.length > 0)  {
                var func = gmap_callbacks.shift();
                if(_.isFunction(func)) {
                    func.apply($window, arguments);
                }
            }
        }
    };

    var service = {
        USER_INTERACTED_EVENT: "GoogleMaps.UserInteracted",
        map: null,
        directionsRenderer: null,
        panel_id: null,
        markers: [],
        lastInfoWindow: null,
        init: function () {
            if(typeof GoogleMaps == "undefined" && !gmap_script_appended) {
                if(_init_called)
                    return;

                _init_called = true;
                Application.loaded.then(function() {
                    var google_maps = document.createElement('script');
                    google_maps.type = "text/javascript";
                    google_maps.src = "https://maps.googleapis.com/maps/api/js?libraries=places&key="+Application.googlemaps_key+"&callback=initGMapCallback";
                    document.body.appendChild(google_maps);
                    gmap_script_appended = true;
                });
            }

            if(gmap_loaded) {
                $window.initGMapCallback();
            }
        },
        addCallback: function(func) {
            gmap_callbacks.push(func);
            service.init();
        },
        createMap: function (element, options) {
            if(!angular.isObject(options))
                options = {};

            if(__self.is_loaded) {
                __self.reset();
            }

            options = _.merge({
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }, options);

            service.map = new google.maps.Map(document.getElementById(element), options);

            google.maps.event.addListener(service.map, "tilesloaded", function() {
                console.log("Maps is loaded");
                __self.is_loaded = true;
            });

            var userInteracted = function(event_name) {
                return function() {
                    $rootScope.$broadcast(service.USER_INTERACTED_EVENT, event_name);
                };
            };

            google.maps.event.addListener(service.map, 'dblclick', userInteracted("dblclick"));
            google.maps.event.addListener(service.map, 'dragend', userInteracted("dragend"));
            google.maps.event.addDomListener(service.map.getDiv(),'mousewheel', userInteracted("wheel"), true);
            google.maps.event.addDomListener(service.map.getDiv(),'DOMMouseScroll', userInteracted("wheel"), true);

            return service.map;

        },
        setCenter: function(coordinates) {
            if(coordinates) {
                var center = new google.maps.LatLng(coordinates.latitude, coordinates.longitude);
                return service.map.setCenter(center);
            } else {
                return $cordovaGeolocation.getCurrentPosition().then(function (position) {
                    service.setCenter(position.coords);
                });
            }
        },
        setPanelId: function(panel_id) {
            service.panel_id = panel_id;
        },
        isLoaded: function() {
            return __self.is_loaded;
        },
        addMarker: function (marker, index) {

            var latlng = new google.maps.LatLng(marker.latitude, marker.longitude);

            var icon = null;

            if (marker.icon && marker.icon.url) {
                var width = marker.icon.width ? marker.icon.width : 95;
                var height = marker.icon.height ? marker.icon.height : 49;
                icon = {
                    url: marker.icon.url,
                    scaledSize: new google.maps.Size(width, height) // Original is 530 x 272
                };
            }

            var options = _.merge({
                position: latlng,
                map: service.map,
                icon: icon
            }, marker.markerOptions);

            var mapMarker = new google.maps.Marker(options);

            if (marker.title) {

                var marker_id = 'info-marker-' + index;

                var infoWindowContent = '<div id="' + marker_id + '"><p style="color:black;">';

                if (marker.link) {
                    infoWindowContent += '<a href="' + marker.link + '">';
                }

                infoWindowContent += marker.title;
                if (marker.link) {
                    infoWindowContent += '</a>';
                }

                var markerHasAction = _.isObject(marker.action) && _.isString(marker.action.label) && _.isFunction(marker.action.onclick);

                if (markerHasAction) {
                    var id = "map_marker_infowindow_action_"+Math.ceil((+new Date())*Math.random());
                    infoWindowContent += '<div style="margin-top: 15px; "><button id="'+id+'" class="button button-custom">'+marker.action.label+'</button></div>';
                }

                infoWindowContent += '</p></div>';

                var infoWindows = new google.maps.InfoWindow({
                    content: infoWindowContent
                });

                if (markerHasAction) {
                    google.maps.event.addListener(infoWindows, 'domready', function () {
                        document.getElementById(id).addEventListener('click', marker.action.onclick);
                    });
                }

                google.maps.event.addListener(mapMarker, 'click', function () {
                    if(service.lastInfoWindow !== null) {
                        service.lastInfoWindow.close();
                    }
                    infoWindows.open(service.map, mapMarker);
                    service.lastInfoWindow = infoWindows;
                    if (marker.hasOwnProperty('onClick') && (typeof marker.onClick === 'function')) {
                        google.maps.event.addDomListener(document.getElementById(marker_id), 'click', function (event) {
                            marker.onClick(angular.extend({}, marker.config));
                        });
                    }
                });
            }

            if (marker.is_centered) {
                service.setCenter(marker);
            }

            if (+index < 0) {
                service.markers.push(mapMarker);
            } else {
                service.markers.splice(index, 0, mapMarker);
            }

            return mapMarker;
        },
        removeMarker: function (mapMarker) {
            var index = service.markers.indexOf(mapMarker);

            if (index >= 0) {
                service.markers[index].setMap(null);
                service.markers.splice(index, 1);
                return true;
            }

            return false;
        },
        replaceMarker: function (mapMarker, marker) {
            if (service.removeMarker(mapMarker)) {
                return service.addMarker(marker);
            }

            return false;
        },
        addRoute: function (route, custom_directions_renderer, custom_panel_div_id) {
            var renderer = null;

            if(_.isObject(custom_directions_renderer) && _.isFunction(custom_directions_renderer.setDirections)) {
                renderer = custom_directions_renderer;
            } else {
                if(!service.directionsRenderer) {
                    service.directionsRenderer = new google.maps.DirectionsRenderer();
                }
                renderer = service.directionsRenderer;
            }

            renderer.setMap(service.map);
            renderer.setDirections(route);
            var panelDiv = document.getElementById(custom_panel_div_id || service.panel_id);
            if(panelDiv) {
                renderer.setPanel(panelDiv);
            }
        },
        fitToBounds: function (bounds) {
            var sw = new google.maps.LatLng(
                bounds.latitudeMin,
                bounds.longitudeMin
            );
            var ne = new google.maps.LatLng(
                bounds.latitudeMax,
                bounds.longitudeMax
            );
            var mapBounds = new google.maps.LatLngBounds(sw, ne);
            service.map.fitBounds(mapBounds);
        },
        geocode: function (address) {

            var deferred = $q.defer();

            if (!this.geocoder) {
                this.geocoder = new google.maps.Geocoder();
            }

            this.geocoder.geocode({
                'address': address
            }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();
                    deferred.resolve({
                        latitude: latitude,
                        longitude: longitude
                    });
                } else {
                    var errorMessage = $translate.instant("The address you're looking for does not exist.");
                    console.error(errorMessage);
                    deferred.reject(errorMessage);
                }
            });

            return deferred.promise;
        },
        reverseGeocode: function (position) {
            var deferred = $q.defer();

            if (!this.geocoder) {
                this.geocoder = new google.maps.Geocoder();
            }

            var latlng = {
                lat: position.latitude,
                lng: position.longitude
            };

            this.geocoder.geocode({
                'location': latlng
            }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    deferred.resolve(results);
                } else {
                    var errorMessage = $translate.instant("The address you're looking for does not exists.");
                    deferred.reject(errorMessage);
                }
            });

            return deferred.promise;
        },
        calculateRoute: function(origin, destination, params, rejectWithResponseAndStatus) {

            var deferred = $q.defer();

            if (origin) {

                __self._calculateRoute(origin, destination, params, rejectWithResponseAndStatus).then(function (route) {
                    deferred.resolve(route);
                }, function (err) {
                    deferred.reject(err);
                });

            } else {

                $cordovaGeolocation.getCurrentPosition().then(function (position) {

                    __self._calculateRoute(position.coords, destination, params, rejectWithResponseAndStatus).then(function (route) {
                        deferred.resolve(route);
                    }, function (err) {
                        deferred.reject(err);
                    });
                }, function (err) {
                    if(
                        angular.isObject(err) && err.code === 1 &&
                            angular.isString(err.message) && err.message.indexOf("secure origin")
                    ) {
                        deferred.reject("Your location could not be found because your application doesn't use SSL.");
                    }
                    deferred.reject("gps_disabled");
                });

            }

            return deferred.promise;
        },
        getBoundsFromPoints: function (points, margin) {

            if (points) {

                var bounds = [[null, null], [null, null]];

                points.reduce(function (output, p) {

                    if (!p.latitude) {
                        console.warn('Invalid latitude.');
                    }

                    if (!p.longitude) {
                        console.warn('Invalid longitude.');
                    }
                    bounds = __self._updateBoundsFromPoints(bounds, p);

                    return output;
                }, []);

                if (points.length !== 0 && margin) {
                    bounds = __self._extendBounds(bounds, margin);
                }
                return {
                    latitudeMin: bounds[0][0],
                    latitudeMax: bounds[1][0],
                    longitudeMin: bounds[0][1],
                    longitudeMax: bounds[1][1]
                };
            }
            return null;
        }
    };

    service.init();

    return service;

});
;/* global
 angular
 */
angular.module('starter').service('layout_10', function ($rootScope) {
    var service = {};

    service.getTemplate = function () {
        return 'templates/home/l10/view.html';
    };

    service.getModalTemplate = function () {
        return 'templates/home/l10/modal.html';
    };

    service.onResize = function () {};

    service.features = function (features, moreButton) {
        var thirdOption = features.overview.options[2];
        var fourthOption = features.overview.options[3];
        // Placing more button at the third place (middle in layout)!
        features.overview.options[2] = moreButton;
        features.overview.options[3] = thirdOption;
        features.overview.options[4] = fourthOption;
        // Removing 4 first option for the modal!
        features.options = features.options.slice(4, features.options.length);

        return features;
    };

    return service;
});
;/* global
 angular
 */
angular.module('starter').service('layout_17', function ($rootScope, $location, $timeout) {
    var service = {};

    service.getTemplate = function () {
        return 'templates/home/l17/view.html';
    };

    service.getModalTemplate = function () {
        return 'templates/home/modal/view.html';
    };

    service.onResize = function () {
        /** Double tap */
        $timeout(function () {
            service._resize();
            $timeout(function () {
                service._resize();
            }, 500);
        }, 100);
    };

    service.features = function (features, more_button) {
        var more_options = features.options.slice(12);
        var chunks = [];
        var i, j, temparray, chunk = 2;
        for (i = 0, j = more_options.length; i < j; i = i + chunk) {
            temparray = more_options.slice(i, i + chunk);
            chunks.push(temparray);
        }
        features.chunks = chunks;

        return features;
    };

    service._resize = function () {
        var scrollview = document.getElementById('metro-scroll');
        if (scrollview) {
            scrollview.style.display = 'block';
        }
        if (document.getElementById('metro-scroll') && document.getElementById('metro-line-2')) {
            var spacing = document.getElementById('metro-scroll').getBoundingClientRect().width / 100 * 2.5;
            var element = document.getElementById('metro-line-2');
            if (element) {
                var positionInfo = element.getBoundingClientRect();
                element.style.height = (positionInfo.width-spacing)/4+'px';
            }
            var lines = document.getElementsByClassName('metro-line');
            for (var i = 0; i < lines.length; i++) {
                lines[i].style.marginBottom = spacing+'px';
            }
        }
    };

    return service;
});
;/*global
    angular
 */
angular.module('starter').service('layout_8', function () {
    var service = {};

    service.getTemplate = function () {
        return 'templates/home/l8/view.html';
    };

    service.getModalTemplate = function () {
        return 'templates/home/modal/view.html';
    };

    service.onResize = function () {};

    service.features = function (features, more_button) {
        var first_option = null;
        var options = [];

        if (features.options.length !== 0) {
            first_option = features.options[0];
            options = features.options.slice(1);
        }

        features.first_option = first_option;
        features.options = options;

        return features;
    };

    return service;
});
;/*global
 angular, DEVICE_TYPE
 */

/**
 * LinkService
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("LinkService", function ($rootScope, $translate, $window, SB) {
    return {
        openLink: function(url, options) {

            if($rootScope.isNotAvailableInOverview() || $rootScope.isNotAvailableOffline()) {
                return;
            }

            //set default options (inapp + navbar)
            /**
             * @todo maybe extend ?
             */
            if(options === undefined) {
                options = {
                    "hide_navbar"       : true,
                    "use_external_app"  : false
                };
            }

            //by default use inappbrowser
            var target = "_blank";
            var inAppBrowserOptions = [];

            switch(true) {

                //On android, tel link are opened in current app
                case (/^(tel:).*/.test(url) && (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID)) :
                    target = "_self";
                    break;

                case options.use_external_app:

                //if PDF, we force use of external application
                case (/.*\.pdf($|\?)/).test(url):

                //On iOS, you cannot hidenavbar and show inappbrowser
                case (options.hide_navbar && (DEVICE_TYPE === SB.DEVICE.TYPE_IOS)):
                    target = "_system";
                    inAppBrowserOptions.push("EnableViewPortScale=yes");
                    break;

                default: 
                    if(options && (options.hide_navbar)) {
                        inAppBrowserOptions.push("location=no");
                        inAppBrowserOptions.push("toolbar=no");
                    } else { //else use standard inAppBrowser with navbar
                        inAppBrowserOptions.push("location=no");
                        inAppBrowserOptions.push("closebuttoncaption=" + $translate.instant("Done"));
                        inAppBrowserOptions.push("transitionstyle=crossdissolve");
                        inAppBrowserOptions.push('toolbar=yes');
                    }

            }
            $window.open(url, target, inAppBrowserOptions.join(","));
        }
    };
});
;/*global
    App, IS_NATIVE_APP, angular
 */

/**
 * Modal
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Loader', function ($ionicLoading, $translate, $state, $timeout, Dialog) {
    var service = {
        is_open : false,
        last_config : '',
        promise : null,
        timeout : null,
        keep_timeout : false,
        timeout_count : 0
    };

    /**
     * Calls the timeout
     */
    service.callTimeout = function () {
        service.timeout = $timeout(function () {
            service.timeout_count = service.timeout_count + 1;
            service.keep_timeout = true;

            var buttons = ["Go back home", "Continue"];
            if (service.timeout_count >= 2) {
                service.keep_timeout = false;
                buttons = ["Go back home"];
            }

            service.hide();

            Dialog.confirm(
                "Feature timeout",
                "It seems the feature your are trying to load is taking too much time!<br />Would you like to continue?",
                buttons)
                .then(function (result) {
                    if (result || (service.timeout_count >= 2)) {
                        service.hide();
                        $state.go("home");
                    } else {
                        /** Calls only twice. */
                        service.show();
                        service.callTimeout();
                    }
                });
        }, 10000);
    };

    /**
     *
     * @param text
     * @param config
     * @param replace
     * @returns {null}
     */
    service.show = function (text, config, replace) {
        if (replace === undefined) {
            replace = false;
        }

        if (!service.is_open) {
            service.is_open = true;

            var template = "<ion-spinner class=\"spinner-custom\"></ion-spinner>";
            if (text !== undefined) {
                if (!replace) {
                    template = "<ion-spinner class=\"spinner-custom\"></ion-spinner><br /><span>" + $translate.instant(text) + "</span>";
                } else {
                    template = $translate.instant(text);
                }
            }

            if (service.last_config === null) {
                service.last_config = angular.extend({
                    template: template
                }, config);
            }

            service.promise = $ionicLoading.show(service.last_config);

            service.timeout_count = 0;
            if (service.keep_timeout === true) {
                service.callTimeout();
            }
        }

        return service.promise;
    };

    /**
     *
     * @returns {*}
     */
    service.hide = function () {
        service.is_open = false;

        if ((service.keep_timeout === false) && (service.timeout !== null)) {
            $timeout.cancel(service.timeout);
            service.timeout = null;
            service.timeout_count = 0;
            service.last_config = null;
        }

        return $ionicLoading.hide();
    };

    return service;
});
;/*global
 App, angular, IS_NATIVE_APP
 */

/**
 * Location, location and coordinates should be acquired fast,
 * we are using timeouts and promise to send answer as fast as possible.
 *
 * @author Xtraball SAS
 *
 */
angular.module('starter').service('Location', function ($cordovaGeolocation, $q) {
    var service = {
        lastFetch: null,
        position: null
    };

    /**
     * Default timeout is 10 seconds
     *
     * @param config
     * @param force
     * @returns {*|promise}
     */
    service.getLocation = function (config, force) {
        var deferred = $q.defer();
        var isResolved = false;

        var localForce = (force !== undefined);

        var localConfig = angular.extend({
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }, config);

        if (!localForce && (service.lastFetch !== null) && ((service.lastFetch + 420000) > Date.now())) {
            // fresh poll, send direct
            deferred.resolve(service.position);
            isResolved = true;
        }

        $cordovaGeolocation.getCurrentPosition(localConfig)
            .then(function (position) {
                service.lastFetch = Date.now();
                service.position = position;
                if (!isResolved) {
                    deferred.resolve(service.position);
                }
            }, function () {
                if (!isResolved) {
                    deferred.reject();
                }
            });

        return deferred.promise;
    };

    /**
     * Returns the latest fetch position, if there is one, or false
     *
     * @returns {null}
     */
    service.getLatest = function () {
        var deferred = $q.defer();

        if (service.lastFetch === null) {
            // Try to fetch it!
            service.getLocation()
                .then(function (position) {
                    deferred.resolve(position);
                }, function () {
                    deferred.reject(false);
                });
        } else {
            deferred.resolve(service.position);
        }

        return deferred.promise;
    };

    return service;
});
;/* global
    App, angular, ionic, MusicControls, DEVICE_TYPE, Audio
 */

/**
 * MediaPlayer
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('MediaPlayer', function ($interval, $rootScope, $state, $log, $stateParams, $timeout,
                                     $translate, $window, Dialog, Loader, SB) {
    var service = {
        media: null,

        is_initialized: false,
        is_minimized: false,
        is_playing: false,
        is_radio: false,
        is_shuffling: false,
        is_stream: false,

        repeat_type: null,

        shuffle_tracks: [],
        tracks: [],

        current_index: 0,
        current_track: null,

        duration: 0,
        elapsed_time: 0,

        value_id: null,

        use_music_controls: (SB.DEVICE.TYPE_BROWSER !== DEVICE_TYPE)
    };

    service.loading = function () {
        var message = $translate.instant('Loading');
        if (service.is_radio) {
            message = $translate.instant('Buffering');
        }

        Loader.show(message);
    };

    var music_controls_events = function (event) {
        switch (event) {
            case 'music-controls-next':
                    // Do something
                    if (!service.is_radio) {
                        service.next();
                    }
                break;
            case 'music-controls-previous':
                    // Do something
                    if (!service.is_radio) {
                        service.prev();
                    }
                break;
            case 'music-controls-pause':
            case 'music-controls-play':
            // External controls (iOS only)
            case 'music-controls-toggle-play-pause' :
                    service.playPause();
                break;
            case 'music-controls-destroy':
                    service.destroy();
                break;

            // Headset events (Android only)
            // All media button events are listed below
            case 'music-controls-media-button' :
                    // Do something
                break;
            case 'music-controls-headset-unplugged':
                    // Do something
                break;
            case 'music-controls-headset-plugged':
                    // Do something
                break;
        }
    };

    service.init = function (tracks_loader, is_radio, track_index) {
        // Destroy service when changing media feature!
        if (service.value_id !== $stateParams.value_id) {
            service.destroy();
        }

        if (service.media && (service.current_track.streamUrl !== tracks_loader.tracks[track_index].streamUrl)) {
            service.destroy();
        }

        if (!service.media) {
            service.value_id = $stateParams.value_id;
            service.is_radio = is_radio;
            service.current_index = track_index;

            if (tracks_loader) {
                service.tracks = tracks_loader.tracks;
            }
        }

        service.is_initialized = true;
        service.openPlayer();

        if (service.use_music_controls) {
            MusicControls.subscribe(music_controls_events);
            MusicControls.listen();
        }
    };

    service.play = function () {
        service.media.play();
        service.is_playing = true;
    };

    service.pre_start = function () {
        if (service.media) {
            service.media.pause();
        }

        service.is_playing = false;
        service.duration = 0;
        service.elapsed_time = 0;
        service.is_media_loaded = false;
        service.is_media_stopped = false;
    };

    service.start = function () {
        service.current_track = service.tracks[service.current_index];

        $log.info(service.current_track, service.tracks);

        if ((service.current_track.streamUrl.indexOf('http://') === -1) &&
            (service.current_track.streamUrl.indexOf('https://') === -1)) {
            Loader.hide();
            Dialog.alert('Error', 'No current stream to load.', 'OK', -1);
            return;
        }

        // Setting the albumCover image
        if (service.current_track.albumCover) {
            service.current_track.albumCover = service.current_track.albumCover.replace('100x100bb', $window.innerWidth + 'x' + $window.innerWidth + 'bb');
        }

        service.is_stream = service.is_radio;
        $log.debug(service.current_track);

        service.media = new Audio(service.current_track.streamUrl);
        service.media.onended = function() {
            service.next();
        };
        service.play();

        service.updateSeekBar();

        Loader.hide();

        service.updateMusicControls();
    };

    service.reset = function () {
        service.media = null;
        service.seekbarTimer = null;
        service.is_shuffling = false;
        service.is_initialized = false;

        service.is_minimized = false;
        $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.HIDE);

        service.repeat_type = null;
        service.current_index = 0;
        service.current_track = null;
        service.shuffle_tracks = [];

        if (service.use_music_controls) {
            MusicControls.destroy();
            MusicControls.subscribe(music_controls_events);
            MusicControls.listen();
        }
    };

    service.destroy = function () {

        $interval.cancel(service.seekbarTimer);
        if (service.media) {
            if (service.is_playing) {
                service.media.pause();
            }
        }

        service.reset();
    };

    service.openPlayer = function () {
        $state.go('media-player', {
            value_id: service.value_id
        });

        service.is_minimized = false;

        $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.HIDE);

        if (!service.media) {
            $timeout(function () {
                service.pre_start();
                service.start();
            }, 1000);
        }
    };

    service.playPause = function () {
        if (service.is_playing) {
            service.media.pause();

            $interval.cancel(service.seekbarTimer);

            if (service.use_music_controls) {
                MusicControls.updateIsPlaying(false);
            }
        } else {
            service.media.play();

            if (service.use_music_controls) {
                MusicControls.updateIsPlaying(true);
            }
        }

        service.is_playing = !service.is_playing;

        service.updateMusicControls();
    };

    service.prev = function () {
        if (service.repeat_type === 'one') {
            service.seekTo(0);
        } else if (service.is_shuffling) {
                if (service.shuffle_tracks.length >= service.tracks.length && service.repeat_type === 'all') {
                    service.shuffle_tracks = [];
                }

                service._randomSong();
            } else if ((service.repeat_type === 'all') && (service.current_index === 0)) {
                service.current_index = service.tracks.length - 1;
            } else if (service.current_index > 0) {
                service.current_index = service.current_index - 1;
            }

        service.pre_start();
        service.start();
    };

    service.next = function () {
        if (service.repeat_type === 'one') {
            service.seekTo(0);
        } else {
            if (service.is_shuffling) {
                if ((service.shuffle_tracks.length >= service.tracks.length) && (service.repeat_type === 'all')) {
                    service.shuffle_tracks = [];
                }

                service._randomSong();
            } else if ((service.repeat_type === 'all') && (service.current_index >= (service.tracks.length - 1))) {
                service.current_index = 0;
            } else if (service.current_index < (service.tracks.length - 1)) {
                service.current_index = service.current_index + 1;
            }

            service.pre_start();
            service.start();
        }
    };

    service._randomSong = function () {
        var random_index = Math.floor(Math.random() * service.tracks.length);

        while ((service.shuffle_tracks.indexOf(random_index) !== -1) || (random_index === service.current_index)) {
            if (service.shuffle_tracks.indexOf(random_index) !== -1) {
                random_index = Math.floor(Math.random() * service.tracks.length);
            } else {
                random_index = random_index + 1;
            }
        }

        if (service.shuffle_tracks.length >= service.tracks.length) {
            random_index = 0;
        }

        service.shuffle_tracks.push(random_index);
        service.current_index = random_index;

        service.updateMusicControls();
    };

    service.backward= function () {
        var tmp_seekto = (service.elapsed_time - 10);
        if (tmp_seekto < 0) {
            service.prev();
        } else {
            service.elapsed_time = tmp_seekto;
        }
        service.seekTo(service.elapsed_time);
    };

    service.forward = function () {
        var tmp_seekto = (service.elapsed_time + 10);
        if (tmp_seekto > service.media.duration) {
            service.next();
        } else {
            service.elapsed_time = tmp_seekto;
        }
        service.seekTo(service.elapsed_time);
    };

    service.willSeek = function () {
        if (service.is_playing) {
            service.media.pause();
            service.is_playing = false;
        }
    };

    service.seekTo = function (position) {
        if (position === 0) {
            service.media.pause();
            service.is_playing = false;
        }
        service.media.currentTime = position;
        if (!service.is_playing) {
            service.playPause();
        }
    };

    service.repeat = function () {
        switch (service.repeat_type) {
            case null:
                service.repeat_type = 'all';
                break;

            case 'all':
                service.repeat_type = 'one';
                break;

            case 'one':
                service.repeat_type = null;
                break;
        }
    };

    service.shuffle = function () {
        service.shuffle_tracks = [];
        service.is_shuffling = !service.is_shuffling;
    };

    service.updateMusicControls = function () {
        if (service.use_music_controls) {
            var hasPrev = true;
            var hasNext = true;
            if (service.is_radio) {
                hasPrev = false;
                hasNext = false;
            }

            if (service.current_index === 0) {
                hasPrev = false;
            }

            if (service.current_index === (service.tracks.length - 1)) {
                hasNext = false;
            }

            MusicControls.create({
                track: service.current_track.name,
                artist: service.current_track.artistName,
                cover: service.current_track.albumCover,
                isPlaying: true,
                dismissable: true,

                hasPrev: hasPrev,
                hasNext: hasNext,
                hasClose: true,

                // iOS only, optional
                album: service.current_track.albumName,
                duration: service.media.duration,
                elapsed: service.media.currentTime,

                // Android only, optional
                ticker: $translate.instant('Now playing ') + service.current_track.name
            }, function () {
                $log.debug('success');
            }, function () {
                $log.debug('error');
            });
        }
    };

    service.updateSeekBar = function () {
        service.seekbarTimer = $interval(function () {
            if (service.is_playing) {
                service.elapsed_time = service.media.currentTime;
            }

            if (!service.is_radio && service.is_media_stopped && service.is_media_loaded) {
                $interval.cancel(service.seekbarTimer);
                service.is_media_stopped = false;
                service.next();
            }
        }, 100);
    };

    return service;
});
;/*global
    App, IS_NATIVE_APP, angular
 */

/**
 * Modal
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Modal', function ($rootScope, $ionicModal, $timeout, $q) {
    var service = {
        is_open                     : false,
        stack                       : [],
        current_modal               : null,
        modal_hidden_subscriber     : null
    };

    /** Listening from $rootScope to prevent external $ionicModal not proxied */
    $rootScope.$on('modal.shown', function () {
        service.is_open = true;

        /** Listening for modal.hidden dynamically */
        service.modal_hidden_subscriber = $rootScope.$on('modal.hidden', function() {
            /** Un-subscribe from modal.hidden RIGHT NOW, otherwise we will create a loop with the automated clean-up */
            service.modal_hidden_subscriber();

            /** Clean-up modal */
            service.current_modal.remove();
            service.current_modal = null;

            /** Unstack next one */
            service.is_open = false;
            service.unStack();
        });
    });

    /**
     * Un stack popups on event
     */
    service.unStack = function () {
        if (service.stack.length >= 1) {
            $timeout(function () {
                var modal = service.stack.shift();

                switch (modal.type) {
                    case 'fromTemplateUrl':
                            service.renderFromTemplateUrl(modal.data);
                        break;
                    case 'fromTemplate':
                            service.renderFromTemplate(modal.data);
                        break;
                }
            }, 250);
        } else {
            service.current_modal = null;

            $timeout(function () {
                return;
            }, 250);
        }
    };

    /**
     *
     * @param templateUrl
     * @param config
     * @returns {*|promise}
     */
    service.fromTemplateUrl = function (templateUrl, config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'fromTemplateUrl',
            data: {
                templateUrl: templateUrl,
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderFromTemplateUrl = function (data) {
        return $ionicModal
            .fromTemplateUrl(data.templateUrl, data.config)
            .then(function (modal) {
                service.current_modal = modal;
                data.promise.resolve(modal);
            });
    };

    /**
     *
     * @param template
     * @param config
     * @returns {*|promise}
     */
    service.fromTemplate = function (template, config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'fromTemplate',
            data: {
                template: template,
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderFromTemplate = function (data) {
        return $ionicModal
            .fromTemplate(data.template, data.config)
            .then(function (modal) {
                service.current_modal = modal;
                data.promise.resolve(modal);
            });
    };

    return service;
});
;/*global
    angular
 */
angular.module("starter").service('MusicTracksLoader', function ($q, $stateParams, MusicTrack) {

    MusicTrack.value_id = $stateParams.value_id;

    var service = {};

    service._filterDuplicatedAlbums = function (albums, albumsIds) {

        if (!albumsIds) {
            albumsIds = [];
        }

        // filter duplicated
        albums = albums.reduce(function (albums, album) {
            if (albumsIds.indexOf(album.id) === -1) {
                albumsIds.push(album.id);
                albums.push(album);
            }
            return albums;
        }, []);

        return albums;
    };

    service.loadTracksFromAlbums = function (albums) {

        var deferred = $q.defer();

        var albumsIds = [];

        // filter duplicated
        albums = service._filterDuplicatedAlbums(albums, albumsIds);

        var tracksLoader = service._buildTracksLoader(albums);

        // read the first tracks
        deferred.resolve(service.readNextTracks(tracksLoader, 50));

        return deferred.promise;

    };

    service.loadTracksFromPlaylists = function (playlists) {

        var deferred = $q.defer();

        var albumsIds = [];

        // get albums from playlists
        var albums = playlists.reduce(function (albums, playlist) {

            // filter duplicated
            var playlistAlbums = service._filterDuplicatedAlbums(playlist.albums, albumsIds);

            // add to list
            albums = albums.concat(playlistAlbums);
            return albums;
        }, []);

        var tracksLoader = service._buildTracksLoader(albums);

        // read the first tracks
        deferred.resolve(service.readNextTracks(tracksLoader, 50));

        return deferred.promise;

    };

    service._buildTracksLoader = function (albums) {

        console.log("_buildTracksLoader", albums);

        return {
            albums: albums,
            tracks: [],
            albumsLoaded: 0,
            errorOccured: false,
            fullyLoaded: function () {
                return this.errorOccured || this.albums.length === this.albumsLoaded;
            },
            loadMore: function (quantity) {
                return service.readNextTracks(this, quantity);
            }
        };
    };


    service._buildTracksLoaderForSingleAlbum = function (album, tracks) {
        var tracksLoader = service._buildTracksLoader([album]);
        tracksLoader.tracks = tracks;
        tracksLoader.albumsLoaded = 1;

        return tracksLoader;
    };

    service.loadSingleTrack = function (track) {
        var tracksLoader = service._buildTracksLoader([]);
        tracksLoader.tracks = [track];
        tracksLoader.albumsLoaded = 0;

        return tracksLoader;
    };
    /**
     * Load the next tracks.
     *
     * Asynchronous service, returns a promise.
     *
     */
    service.readNextTracks = function (tracksLoader, quantityToLoad) {

        var tracksLoaded = [];

        return service.readNextTracksRecursive(tracksLoaded, tracksLoader, quantityToLoad);

    };

    /**
     * Load the next tracks (recursive).
     *
     * Asynchronous service, returns a promise.
     *
     * Recursive call until the number of tracks to load is reached or all albums have been loaded.
     */
    service.readNextTracksRecursive = function (tracksLoaded, tracksLoader, quantityToLoad) {

        var deferred = $q.defer();

        var maxAlbumsToLoadAtOnce = Math.ceil((quantityToLoad - tracksLoaded.length) / 15);
        if (maxAlbumsToLoadAtOnce === 0) {
            maxAlbumsToLoadAtOnce = 1;
        }

        service.readNextAlbumsTracks(tracksLoader, maxAlbumsToLoadAtOnce).then(function (result) {

                tracksLoaded = tracksLoaded.concat(result.tracksLoaded);

                if (!tracksLoader.fullyLoaded() && tracksLoaded.length < quantityToLoad) {

                    // delegate the resolution of the deferred to the next recursive method
                    deferred.resolve(service.readNextTracksRecursive(tracksLoaded, tracksLoader, quantityToLoad, maxAlbumsToLoadAtOnce));

                } else {
                    // last call, resolve all the recursion chain
                    deferred.resolve({
                        tracksLoader: tracksLoader,
                        tracksLoaded: tracksLoaded
                    });

                }

            },
            function (err) {
                console.error('Error while loading tracks.', err);
                deferred.reject(err);
                tracksLoader.errorOccured = true;
            });

        return deferred.promise;

    };

    /**
     * Load the next albums tracks (max : maxAlbumsToLoad).
     *
     * Asynchronous service, returns a promise.
     */
    service.readNextAlbumsTracks = function (tracksLoader, maxAlbumsToLoad) {

        var deferred = $q.defer();

        var promises = [];
        for (var i = 0; i < maxAlbumsToLoad && !tracksLoader.fullyLoaded(); i++, tracksLoader.albumsLoaded++) {
            var album = tracksLoader.albums[tracksLoader.albumsLoaded];
            var param = {};
            if(album.element == "album") {
                param.album_id = album.id;
            } else {
                param.track_id = album.id;
            }
            promises.push(MusicTrack.findByAlbum(param));
        }

        // synchronize all queries
        try {
            $q.all(promises)
                .then(function (tracksResponses) {

                        var tracksLoaded = tracksResponses.reduce(function (tracks, tracksResponse) {
                            tracks = tracks.concat(tracksResponse.tracks);

                            return tracks;
                        }, []);

                        tracksLoader.tracks = tracksLoader.tracks.concat(tracksLoaded);

                        deferred.resolve({
                            tracksLoader: tracksLoader,
                            tracksLoaded: tracksLoaded
                        });
                    },
                    function (err) {
                        console.error('Error while loading tracks.', err);
                        deferred.reject(err);
                    }).finally(function () {});
        } catch(e) {
            console.error(e.message);
        }


        return deferred.promise;

    };

    return service;

});;/*global
    angular, DEVICE_TYPE, Camera, CameraPopoverOptions, FileReader
*/

/**
 * Picture
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Picture', function ($cordovaCamera, $ionicActionSheet, $q, $rootScope,
                                                      $translate, Dialog, SB) {
    var service = {
        isOpen: false,
        sheetResolver: null,
        stack: []
    };

    /**
     * @param width
     * @param height
     * @param quality
     */
    service.takePicture = function (width, height, quality) {
        if (service.isOpen || $rootScope.isNotAvailableInOverview()) {
            return $q.reject();
        }

        if (Camera === undefined) {
            Dialog.alert('Error', 'Camera is not available.', 'OK', -1)
                .then(function () {
                    return $q.reject();
                });
            return $q.reject();
        }

        service.isOpen = true;

        var deferred = $q.defer();

        var localWidth = (width === undefined) ? 1200 : width;
        var localHeight = (height === undefined) ? 1200 : height;
        var localQuality = (quality === undefined) ? 90 : quality;

        var sourceType = Camera.PictureSourceType.CAMERA;

        var _buttons = [
            {
                text: $translate.instant('Import from Library')
            }
        ];

        if (DEVICE_TYPE !== SB.DEVICE.TYPE_BROWSER) {
            _buttons.unshift({
                text: $translate.instant('Take a picture')
            });
        }

        service.sheetResolver = $ionicActionSheet.show({
            buttons: _buttons,
            cancelText: $translate.instant('Cancel'),
            cancel: function () {
                service.sheetResolver();

                deferred.reject({
                    message: $translate.instant('Cancelled')
                });

                service.isOpen = false;
            },
            buttonClicked: function (index) {
                if (index === 0) {
                    sourceType = Camera.PictureSourceType.CAMERA;
                }

                if (index === 1) {
                    sourceType = Camera.PictureSourceType.PHOTOLIBRARY;
                }

                var options = {
                    quality: localQuality,
                    destinationType: Camera.DestinationType.DATA_URL,
                    sourceType: sourceType,
                    encodingType: Camera.EncodingType.JPEG,
                    targetWidth: localWidth,
                    targetHeight: localHeight,
                    correctOrientation: true,
                    popoverOptions: CameraPopoverOptions,
                    saveToPhotoAlbum: false
                };

                if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                    var input = angular.element('<input type="file" accept="image/*">');
                    var selectedFile = function (selectEvent) {
                        var file = selectEvent.currentTarget.files[0];
                        var reader = new FileReader();
                        reader.onload = function (onloadEvent) {
                            input.off('change', selectedFile);

                            if (onloadEvent.target.result.length > 0) {
                                service.sheetResolver();

                                deferred.resolve({
                                    image: onloadEvent.target.result
                                });

                                service.isOpen = false;
                            } else {
                                service.sheetResolver();
                                service.isOpen = false;
                            }
                        };
                        reader.onerror = function () {
                            service.sheetResolver();

                            Dialog.alert('Error', 'An error occurred while loading the picture.', 'OK', -1)
                                .then(function () {
                                    service.isOpen = false;
                                });
                        };
                        reader.readAsDataURL(file);
                    };
                    input.on('change', selectedFile);
                    input[0].click();
                } else {
                    $cordovaCamera.getPicture(options)
                        .then(function (imageData) {
                            service.sheetResolver();

                            deferred.resolve({
                                image: 'data:image/jpeg;base64,' + imageData
                            });

                            service.isOpen = false;
                        }, function (error) {
                            service.sheetResolver();

                            Dialog.alert('Error', 'An error occurred while taking a picture.', 'OK', -1)
                                .then(function () {
                                    service.isOpen = false;
                                });

                            deferred.reject({
                                message: error
                            });
                        }).catch(function (error) {
                            service.sheetResolver();

                            Dialog.alert('Error', 'An error occurred while taking a picture.', 'OK', -1)
                                .then(function () {
                                    service.isOpen = false;
                                });

                            deferred.reject({
                                message: error
                            });
                        });
                }

                return true;
            }
        });

        return deferred.promise;
    };

    return service;
});
;/*global
 angular, ProgressBar
 */

/**
 * ProgressBar
 *
 * @author Xtraball SAS
 *
 * @note wrapper to lazyload/get progressbar js
 */
angular.module('starter').service('ProgressbarService', function ($ocLazyLoad) {
    var service = {
        config: {
            trail: '#eee',
            bar_text: '#aaa'
        },
        progress_bar: null
    };

    service.init = function (config) {
        service.config = config;

        return $ocLazyLoad.load('./js/libraries/progressbar.min.js');
    };

    service.createCircle = function (container) {
        service.progress_bar = new ProgressBar.Circle(container, {
            color: service.config.bar_text,
            strokeWidth: 2.6,
            trailWidth: 2,
            trailColor: service.config.trail,
            easing: 'easeInOut',
            duration: 1000,
            text: {
                autoStyleContainer: false
            },
            from: {
                color: service.config.bar_text,
                width: 2.6
            },
            to: {
                color: service.config.bar_text,
                width: 2.6
            },
            step: function (state, circle) {
                circle.path.setAttribute('stroke', state.color);
                circle.path.setAttribute('stroke-width', state.width);

                var value = Math.round(circle.value() * 100);
                if (value === 0) {
                    circle.setText('');
                } else {
                    circle.setText(value);
                }
            }
        });
    };

    /**
     *
     * @param progress 0-1
     */
    service.updateProgress = function (progress) {
        if (service.progress_bar !== null) {
            service.progress_bar.animate(progress);
        }
    };

    service.remove = function () {
        if (service.progress_bar !== null) {
            service.progress_bar.destroy();
            service.progress_bar = null;
        }
    };

    return service;
});
;/*global
    App, DOMAIN, angular, btoa, device, cordova, calculateDistance
 */

/**
 * PushService
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('PushService', function ($cordovaLocalNotification, $location, $log, $q, $rootScope,
                                                           $translate, $window, $session, Application, Dialog,
                                                           LinkService, Pages, Push, SB) {
    var service = {
        push: null,
        settings: {
            android: {
                senderID: '01234567890',
                icon: 'ic_icon',
                iconColor: '#0099C7'
            },
            ios: {
                clearBadge: true,
                alert: true,
                badge: true,
                sound: true
            },
            windows: {}
        }
    };

    /**
     * Configure Push Service
     *
     * @param senderID
     * @param iconColor
     */
    service.configure = function (senderID, iconColor) {
        // senderID error proof for Android!
        if ((Push.device_type === SB.DEVICE.TYPE_ANDROID) &&
            (senderID === '01234567890' || senderID ==='')) {
            $log.debug('Invalid senderId: ' + senderID);
            service.settings.android.senderID = null;
        } else {
            service.settings.android.senderID = senderID;
        }

        // Validating push color!
        if (!(/^#[0-9A-F]{6}$/i).test(iconColor)) {
            $log.debug('Invalid iconColor: ' + iconColor);
        } else {
            service.settings.android.iconColor = iconColor;
        }
    };

    /**
     * If available, initialize push
     */
    service.init = function () {
        if (!$window.PushNotification) {
            return;
        }

        service.push = $window.PushNotification.init(service.settings);
    };

    /**
     * Handle registration, and various push events
     */
    service.register = function () {
        service.init();

        if (service.push && $rootScope.isNativeApp) {
            service.push.on('registration', function (data) {
                $log.debug('device_token: ' + data.registrationId);

                Push.device_token = data.registrationId;
                service.registerDevice();
            });

            service.onNotificationReceived();
            service.push.on('error', function (error) {
                $log.debug(error.message);
            });

            service.updateUnreadCount();

            Application.loaded.then(function () {
                // When Application is loaded, and push registered, look for missed push!
                service.fetchMessagesOnStart();

                // Register for push events!
                $rootScope.$on(SB.EVENTS.PUSH.notificationReceived, function (event, data) {
                    // Refresh to prevent the need for pullToRefresh!
                    var pushFeature = _.filter(Pages.getActivePages(), function (page) {
                        return (page.code === 'push_notification');
                    });
                    if (pushFeature.length >= 1) {
                        Push.setValueId(pushFeature[0].value_id);
                        Push.findAll(0, true);
                    }

                    service.displayNotification(data);
                });
            });
        } else {
            $log.debug('Unable to initialize push service.');
        }
    };

    service.registerDevice = function () {
        switch (Push.device_type) {
            case SB.DEVICE.TYPE_ANDROID:
                service.registerAndroid();
                break;

            case SB.DEVICE.TYPE_IOS:
                service.registerIos();
                break;
        }
    };

    service.registerAndroid = function () {
        var params = {
            app_id: Application.app_id,
            app_name: Application.app_name,
            registration_id: btoa(Push.device_token)
        };
        Push.registerAndroidDevice(params);
    };

    service.registerIos = function () {
        cordova.getAppVersion.getVersionNumber()
            .then(function (appVersion) {
                var deviceName = null;
                try {
                    deviceName = device.platform;
                } catch (e) {
                    $log.debug(e.message);
                }

                var deviceModel = null;
                try {
                    deviceModel = device.model;
                } catch (e) {
                    $log.debug(e.message);
                }

                var deviceVersion = null;
                try {
                    deviceVersion = device.version;
                } catch (e) {
                    $log.debug(e.message);
                }

                var params = {
                    app_id: Application.app_id,
                    app_name: Application.app_name,
                    app_version: appVersion,
                    device_token: Push.device_token,
                    device_name: deviceName,
                    device_model: deviceModel,
                    device_version: deviceVersion,
                    push_badge: 'enabled',
                    push_alert: 'enabled',
                    push_sound: 'enabled'
                };

                Push.registerIosDevice(params);
            });
    };

    service.onNotificationReceived = function () {
        service.push.on('notification', function (data) {
            if (data.additionalData.longitude && data.additionalData.latitude) {
                var callbackCurrentPosition = function (result) {
                    var distance_in_km = calculateDistance(
                        result.latitude,
                        result.longitude,
                        data.additionalData.latitude,
                        data.additionalData.longitude,
                        'K'
                    );

                    if (distance_in_km <= data.additionalData.radius) {
                        if (Push.device_type === SB.DEVICE.TYPE_IOS) {
                            data.title = data.additionalData.user_info.alert.body;
                            data.message = data.title;
                        }

                        service.sendLocalNotification(data.additionalData.message_id, data.title, data.message);

                        $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, data);
                    } else {
                        service.addProximityAlert(data);
                    }
                };

                var callbackErrCurrentPosition = function (err) {
                    $log.debug(err.message);

                    service.addProximityAlert(data);
                };

                if (Push.device_type === SB.DEVICE.TYPE_IOS) {
                    $window.BackgroundGeolocation
                        .getCurrentPosition(function (location, taskId) {
                            location.latitude = location.coords.latitude;
                            location.longitude = location.coords.longitude;

                            callbackCurrentPosition(location);
                            $window.BackgroundGeolocation.finish(taskId);
                        }, callbackErrCurrentPosition);
                } else {
                    // Get the user current position when app on foreground!
                    $window.BackgroundGeoloc.getCurrentPosition(callbackCurrentPosition, callbackErrCurrentPosition);
                }
            } else {
                $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, data);
            }

            service.push.finish(function () {
                $log.debug('push finish success');
                // success!
            }, function () {
                $log.debug('push finish error');
                // error!
            });
        });
    };

    service.startBackgroundGeolocation = function () {
        if (!$window.BackgroundGeolocation) {
            $log.debug('unable to find BackgroundGeolocation plugin.');
            return;
        }

        switch (Push.device_type) {
            case SB.DEVICE.TYPE_IOS:

                $log.debug('-- iOS StartBackgroundLocation --');
                service.startIosBackgroundGeolocation();

                break;

            case SB.DEVICE.TYPE_ANDROID:

                $log.debug('-- ANDROID StartBackgroundLocation --');

                $window.BackgroundGeoloc.startBackgroundLocation(function (result) {
                    // Android only!
                    var proximity_alerts = JSON.parse(localStorage.getItem('proximity_alerts'));
                    if (proximity_alerts !== null) {
                        angular.forEach(proximity_alerts, function (value, index) {
                            var alert = value;

                            var distance_in_km = calculateDistance(result.latitude,
                                result.longitude, alert.additionalData.latitude, alert.additionalData.longitude, 'K');
                            if (distance_in_km <= alert.additionalData.radius) {
                                var current_date = Date.now();
                                var push_date = new Date(alert.additionalData.send_until).getTime();

                                if (!push_date || (push_date >= current_date)) {
                                    service.sendLocalNotification(alert.additionalData.message_id, alert.title, alert.message);

                                    $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, alert);
                                }

                                proximity_alerts.splice(index, 1);
                            }
                        });

                        localStorage.setItem('proximity_alerts', JSON.stringify(proximity_alerts));
                    } else {
                        $window.BackgroundGeoloc.stopBackgroundLocation();
                    }
                }, function (err) {
                    $log.debug('error to startLocation: ' + err);
                });

                break;
        }
    };

    service.startIosBackgroundGeolocation = function () {
        // This callback will be executed every time a geolocation is recorded in the background!
        var callbackFn = function (location, taskId) {
            var coords = location.coords;
            var lat = coords.latitude;
            var lng = coords.longitude;
            $log.debug('- Location: ', JSON.stringify(location));

            // Must signal completion of your callbackFn.
            $window.BackgroundGeolocation.finish(taskId);
        };

        // This callback will be executed if a location-error occurs.  Eg: this will be called if user disables location-services.
        var failureFn = function (errorCode) {
            $log.debug('- BackgroundGeoLocation error: ', errorCode);
        };

        $window.BackgroundGeolocation.onGeofence(function (params, taskId) {
            try {
                // var location  = params.location;
                var identifier = params.identifier;
                var message_id = identifier.replace('push', '');
                var action = params.action;

                $log.debug('A geofence has been crossed: ', identifier);
                $log.debug('ENTER or EXIT ?: ', action);

                // Remove the geofence
                $window.BackgroundGeolocation.removeGeofence(identifier);

                // Remove the stored proximity alert
                var proximity_alerts = JSON.parse(localStorage.getItem('proximity_alerts'));
                if (proximity_alerts !== null) {
                    angular.forEach(proximity_alerts, function (value, index) {
                        var alert = value;

                        if (message_id === alert.additionalData.message_id) {
                            var current_date = Date.now();
                            var push_date = new Date(alert.additionalData.send_until).getTime();

                            if (!push_date || (push_date >= current_date)) {
                                alert.title = alert.additionalData.user_info.alert.body;
                                alert.message = alert.title;

                                service.sendLocalNotification(alert.additionalData.message_id, alert.title, alert.message);

                                $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, alert);
                            }

                            proximity_alerts.splice(index, 1);
                        }
                    });

                    localStorage.setItem('proximity_alerts', JSON.stringify(proximity_alerts));
                }
            } catch (e) {
                $log.debug('An error occurred in my application code', e);
            }

            $window.BackgroundGeolocation.finish(taskId);
        });

        // BackgroundGeoLocation is highly configurable!
        $window.BackgroundGeolocation.configure({
            // Geolocation config!
            desiredAccuracy: 0,
            distanceFilter: 10,
            stationaryRadius: 50,
            locationUpdateInterval: 1000,
            fastestLocationUpdateInterval: 5000,

            // Activity Recognition config!
            activityType: 'AutomotiveNavigation',
            activityRecognitionInterval: 5000,
            stopTimeout: 5,

            // Disable aggressive GPS!
            disableMotionActivityUpdates: true,

            // Block mode!
            useSignificantChangesOnly: true,

            // Application config!
            debug: false,
            stopOnTerminate: true,
            startOnBoot: true
        }, function (state) {
            $log.debug('BackgroundGeolocation ready: ', state);
            if (!state.enabled) {
                $window.BackgroundGeolocation.start();
            }
        });
    };

    /**
     * Geofencing for iOS/Background geolocation for Android
     *
     * @param data
     */
    service.addProximityAlert = function (data) {
        $log.debug('-- Adding a proximity alert --');

        var proximityAlerts = localStorage.getItem('proximity_alerts');
        var jsonProximityAlerts = JSON.parse(proximityAlerts);

        if (jsonProximityAlerts === null) {
            jsonProximityAlerts = [];
            jsonProximityAlerts.push(data);
            localStorage.setItem('proximity_alerts', JSON.stringify(jsonProximityAlerts));
        } else {
            var index = proximityAlerts.indexOf(JSON.stringify(data));
            if (index === -1) {
                jsonProximityAlerts.push(data);
                localStorage.setItem('proximity_alerts', JSON.stringify(jsonProximityAlerts));
            }
        }

        switch (Push.device_type) {
            case SB.DEVICE.TYPE_IOS:

                    $log.debug('-- iOS --');
                    $window.BackgroundGeolocation.addGeofence({
                        identifier: 'push' + data.additionalData.message_id,
                        radius: Number.parseInt(data.additionalData.radius * 1000, 10),
                        latitude: data.additionalData.latitude,
                        longitude: data.additionalData.longitude,
                        notifyOnEntry: true
                    }, function () {
                        $log.debug('Successfully added geofence');
                    }, function (error) {
                        $log.debug('Failed to add geofence', error);
                    });

                break;

            case SB.DEVICE.TYPE_ANDROID:

                    $log.debug('-- ANDROID --');
                    service.startBackgroundGeolocation();

                break;
        }
    };

    /**
     * Update push badge.
     */
    service.updateUnreadCount = function () {
        Push.updateUnreadCount()
            .then(function (data) {
                Push.unread_count = data.unread_count;
                $rootScope.$broadcast(SB.EVENTS.PUSH.unreadPush);
            });
    };

    /**
     * LocalNotification wrapper.
     *
     * @param messageId
     * @param title
     * @param message
     */
    service.sendLocalNotification = function (messageId, title, message) {
        $log.debug('-- Push-Service, sending a Local Notification --');

        var localMessage = angular.copy(message);
        if (Push.device_type === SB.DEVICE.TYPE_IOS) {
            localMessage = '';
        }

        var params = {
            id: messageId,
            title: title,
            text: localMessage
        };

        if (Push.device_type === SB.DEVICE.TYPE_ANDROID) {
            params.icon = 'res://icon.png';
        }

        $cordovaLocalNotification.schedule(params);

        Push.markAsDisplayed(messageId);
    };

    /**
     * Trying to fetch latest Push & InApp messages on app Start.
     */
    service.fetchMessagesOnStart = function () {
        Push.getLastMessages(false)
            .then(function (data) {
                // Last push!
                var push = data.push_message;
                if (push) {
                    service.displayNotification(push);
                }

                // Last InApp Message!
                var inappMessage = data.inapp_message;
                if (inappMessage) {
                    inappMessage.type = 'inapp';
                    inappMessage.message = inappMessage.text;
                    inappMessage.config = {
                        buttons: [
                            {
                                text: $translate.instant('OK'),
                                type: 'button-custom',
                                onTap: function () {
                                    Push.markInAppAsRead();
                                }
                            }
                        ]
                    };

                    if ((inappMessage.cover !== null) && (inappMessage.additionalData === undefined)) {
                        inappMessage.additionalData = {
                            cover: inappMessage.cover
                        };
                    }

                    service.displayNotification(inappMessage);
                }
        });
    };

    /**
     * Displays a notification to the user
     *
     * @param {Object} messagePayload

     * @returns Promise
     */
    service.displayNotification = function (messagePayload) {
        $log.debug('PUSH messagePayload', messagePayload);

        // Prevent an ID being shown twice.
        $session.getItem('pushMessageIds')
            .then(function (pushMessageIds) {
                var localPushMessageIds = pushMessageIds;
                if (pushMessageIds === null || !Array.isArray(pushMessageIds)) {
                    localPushMessageIds = [];
                }

                var messageId = parseInt(messagePayload.additionalData.message_id, 10);
                if (localPushMessageIds.indexOf(messageId) === -1) {
                    // Store acknowledged messages in localstorage.
                    localPushMessageIds.push(messageId);
                    $session.setItem('pushMessageIds', localPushMessageIds);

                    var extendedPayload = messagePayload.additionalData;

                    if ((extendedPayload !== undefined) &&
                        (extendedPayload.cover || extendedPayload.action_value)) {
                        // Prevent missing or not base url!
                        var coverUri = extendedPayload.cover;
                        try {
                            if (coverUri.indexOf('http') !== 0) {
                                coverUri = DOMAIN + extendedPayload.cover;
                            }
                        } catch (e) {
                            // No cover!
                        }

                        var isInAppMessage = ((messagePayload.type !== undefined) &&
                            (messagePayload.type === 'inapp'));

                        var config = {
                            buttons: [
                                {
                                    text: $translate.instant('Cancel'),
                                    type: 'button-custom',
                                    onTap: function () {
                                        if (isInAppMessage) {
                                            Push.markInAppAsRead();
                                        }
                                        // Simply closes!
                                    }
                                },
                                {
                                    text: $translate.instant('View'),
                                    type: 'button-custom',
                                    onTap: function () {
                                        if (isInAppMessage) {
                                            Push.markInAppAsRead();
                                        }

                                        if ((extendedPayload.open_webview !== true) &&
                                            (extendedPayload.open_webview !== 'true')) {
                                            $location.path(extendedPayload.action_value);
                                        } else {
                                            LinkService.openLink(extendedPayload.action_value);
                                        }
                                    }
                                }
                            ],
                            cssClass: 'push-popup',
                            title: messagePayload.title,
                            template:
                            '<div class="list card">' +
                                '<div class="item item-image' + (extendedPayload.cover ? '' : ' ng-hide') + '">' +
                                    '<img src="' + (coverUri) + '">' +
                                '</div>' +
                                '<div class="item item-custom">' +
                                    '<span>' + messagePayload.message + '</span>' +
                                '</div>' +
                            '</div>'
                        };

                        if (messagePayload.config !== undefined) {
                            config = angular.extend(config, messagePayload.config);
                        }

                        // Handles case with only a cover image!
                        if ((extendedPayload.action_value === undefined) ||
                            (extendedPayload.action_value === '') ||
                            (extendedPayload.action_value === null)) {
                            config.buttons = [
                                {
                                    text: $translate.instant('OK'),
                                    type: 'button-custom',
                                    onTap: function () {
                                        // Simply closes!
                                    }
                                }
                            ];
                        }

                        $log.debug('Message payload (ionicPopup):', messagePayload, config);
                        Dialog.ionicPopup(config);
                    } else {
                        var localTitle = (messagePayload.title !== undefined) ?
                            messagePayload.title : 'Notification';
                        $log.debug('Message payload (alert):', messagePayload);
                        Dialog.alert(localTitle, messagePayload.message, 'OK');
                    }

                    // Search for less resource consuming maybe use Push factory directly!
                    $rootScope.$broadcast(SB.EVENTS.PUSH.unreadPushs, messagePayload.count);
                }

                // Nope!
                $log.debug('Will not display duplicated message: ', messagePayload);
            }).catch(function (err) {
                // we got an error
                $log.debug('We got an error with the localForage when trying to display push message: ', messagePayload);
                $log.debug(err);
            });
    };

    return service;
});
;/*global
 angular, localStorage, device
 */

angular.module('starter').service('$session', function ($log, $pwaCache, $q, $window) {
    $log.debug('Init once $session');

    var service = {
        localstorage_key    : 'sb-auth-token',
        session_id          : false,
        device_uid          : null,
        device_width        : 512,
        device_height       : 512,
        device_orientation  : 'portrait',
        is_loaded           : false,
        resolver            : $q.defer()
    };

    /** Be sure the session is loaded */
    Object.defineProperty(service, 'loaded', {
        get: function () {
            if (service.is_loaded) {
                return $q.resolve();
            }
            return service.resolver.promise;
        },
        set: function (value) {
            service.is_loaded = !!value;
            if (service.is_loaded === true) {
                service.resolver.resolve();
            }
        }
    });

    /**
     *
     * @param sessionId
     */
    service.setId = function (sessionId) {
        if ((sessionId === 'undefined') ||
            (sessionId === undefined) ||
            (sessionId === 'null') ||
            (sessionId === null) ||
            (sessionId === '')) {
            $log.error('Not saving invalid session_id: ', sessionId);
            return;
        }

        service.session_id = sessionId;
        service.setItem(service.localstorage_key, sessionId);

        /** Fallback */
        $window.localStorage.setItem('sb-auth-token', sessionId);

        service.setDeviceUid();
    };

    /**
     * @returns string|false
     */
    service.getId = function () {
        if ((service.session_id === 'undefined') ||
            (service.session_id === undefined) ||
            (service.session_id === 'null') ||
            (service.session_id === null) ||
            (service.session_id === '')) {
            return false;
        }
        return service.session_id;
    };

    /**
     *
     */
    service.setDeviceUid = function () {
        if ($window.device === undefined) {
            service.device_uid = 'unknown_' + service.getId();
        } else {
            if ($window.device.platform === 'browser') {
                service.device_uid = 'browser_' + service.getId();
            } else {
                service.device_uid = $window.device.uuid;
            }
        }

        /** And finally if we really don't get it */
        if (service.device_uid ==='""' || service.device_uid === undefined) {
            service.device_uid = 'unknown_' + service.getId();
        }
    };

    service.getDeviceUid = function () {
        return service.device_uid;
    };

    /**
     * clear the current session
     */
    service.clear = function () {
        service.session_id = '';
        service.removeItem(service.localstorage_key);
    };

    /**
     *
     * @param width
     * @param height
     * @returns {{width: number, height: number, orientation: string}}
     */
    service.setDeviceScreen = function (width, height) {
        var orientation = ($window.matchMedia('(orientation: portrait)').matches) ? 'portrait' : 'landscape';

        service.device_width = width;
        service.device_height = height;
        service.device_orientation = orientation;

        return service.getDeviceScreen();
    };

    /**
     *
     * @returns {{width: number, height: number, orientation: string}}
     */
    service.getDeviceScreen = function () {
        return {
            width: service.device_width,
            height: service.device_height,
            orientation: service.device_orientation
        };
    };

    /**
     * save item.
     */
    service.setItem = function (key, value) {
        return $pwaCache.getRegistryCache().setItem(key, value);
    };

    /**
     * get item.
     */
    service.getItem = function (key) {
        return $pwaCache.getRegistryCache().getItem(key);
    };

    /**
     * remove item.
     */
    service.removeItem = function (key) {
        return $pwaCache.getRegistryCache().removeItem(key);
    };

    /**
     * Init once
     */
    service.setDeviceScreen($window.innerWidth, $window.innerHeight);
    service.getItem(service.localstorage_key)
        .then(function (value) {
            var fallback = $window.localStorage.getItem('sb-auth-token');

            if ((value !== null) && (value !== undefined)) {
                $log.debug('Set once $session from pwaRegistry on start: ', value);
                service.setId(value);

                // Don't forget to log-in the customer.!
            } else if ((fallback !== null) && (fallback !== undefined)) {
                $log.debug('Set once $session from fallback localstorage on start: ', fallback);

                service.setId(fallback);
            }

            if (service.device_uid === null) {
                service.setDeviceUid();
            }

            service.loaded = true;
        });

    return service;
});
;/*global
    App, DOMAIN
 */

/**
 * SocialSharing
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("SocialSharing", function($cordovaSocialSharing, $translate, $q, Application) {

    var service = {
        is_sharing: false
    };

    /**
     * Unified social sharing
     *
     * @param content
     * @param message
     * @param subject
     * @param link
     * @param file
     */
    service.share = function(content, message, subject, link, file) {

        if(service.is_sharing) {
            return;
        }

        service.is_sharing = true;

        if(content === undefined) {
            content = "this";
        }

        /** For mobile */
        var download_app_link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;

        /** Generic message */
        var generic_message = $translate.instant("Hi. I just found $1 in the $2 app.")
                                .replace("$1", content)
                                .replace("$2", Application.app_name);

        if(message !== undefined) {
            message = $translate.instant(message)
                .replace("$1", content)
                .replace("$2", Application.app_name);
        }

        var _link       = (link === undefined) ? download_app_link : link;
        var _file       = (file === undefined) ? "" : file;
        var _message    = (message === undefined) ? generic_message : message;
        var _subject    = (subject === undefined) ? "" : subject;

        var deferred = $q.defer();

        $cordovaSocialSharing
            .share(_message, _subject, _file, _link)
            .then(function (result) {
                deferred.resolve(result);
                service.is_sharing = false;
            }, function (error) {
                deferred.reject(error);
                service.is_sharing = false;
            });

        return deferred.promise;

    };

    return service;
});;/**
 * Angular Dynamic Locale - 0.1.32
 * https://github.com/lgalfaso/angular-dynamic-locale
 * License: MIT
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module unless amdModuleId is set
        define([], function () {
            return (factory());
        });
    } else if (typeof exports === 'object') {
        // Node. Does not work with strict CommonJS, but
        // only CommonJS-like environments that support module.exports,
        // like Node.
        module.exports = factory();
    } else {
        factory();
    }
}(this, function () {
    'use strict';
    angular.module('tmh.dynamicLocale', []).config(['$provide', function($provide) {
        function makeStateful($delegate) {
            $delegate.$stateful = true;
            return $delegate;
        }

        $provide.decorator('dateFilter', ['$delegate', makeStateful]);
        $provide.decorator('numberFilter', ['$delegate', makeStateful]);
        $provide.decorator('currencyFilter', ['$delegate', makeStateful]);

    }])
        .constant('tmhDynamicLocale.STORAGE_KEY', 'tmhDynamicLocale.locale')
        .provider('tmhDynamicLocale', ['tmhDynamicLocale.STORAGE_KEY', function(STORAGE_KEY) {

            var defaultLocale,
                localeLocationPattern = 'angular/i18n/angular-locale_{{locale}}.js',
                nodeToAppend,
                storageFactory = 'tmhDynamicLocaleStorageCache',
                storage,
                storageKey = STORAGE_KEY,
                promiseCache = {},
                activeLocale,
                extraProperties = {};

            /**
             * Loads a script asynchronously
             *
             * @param {string} url The url for the script
             @ @param {function} callback A function to be called once the script is loaded
             */
            function loadScript(url, callback, errorCallback, $timeout) {
                var script = document.createElement('script'),
                    element = nodeToAppend ? nodeToAppend : document.getElementsByTagName("body")[0],
                    removed = false;

                script.type = 'text/javascript';
                if (script.readyState) { // IE
                    script.onreadystatechange = function () {
                        if (script.readyState === 'complete' ||
                            script.readyState === 'loaded') {
                            script.onreadystatechange = null;
                            $timeout(
                                function () {
                                    if (removed) return;
                                    removed = true;
                                    element.removeChild(script);
                                    callback();
                                }, 30, false);
                        }
                    };
                } else { // Others
                    script.onload = function () {
                        if (removed) return;
                        removed = true;
                        element.removeChild(script);
                        callback();
                    };
                    script.onerror = function () {
                        if (removed) return;
                        removed = true;
                        element.removeChild(script);
                        errorCallback();
                    };
                }
                script.src = url;
                script.async = true;
                element.appendChild(script);
            }

            /**
             * Loads a locale and replaces the properties from the current locale with the new locale information
             *
             * @param {string} localeUrl The path to the new locale
             * @param {Object} $locale The locale at the curent scope
             * @param {string} localeId The locale id to load
             * @param {Object} $rootScope The application $rootScope
             * @param {Object} $q The application $q
             * @param {Object} localeCache The current locale cache
             * @param {Object} $timeout The application $timeout
             */
            function loadLocale(localeUrl, $locale, localeId, $rootScope, $q, localeCache, $timeout) {

                function overrideValues(oldObject, newObject) {
                    if (activeLocale !== localeId) {
                        return;
                    }
                    angular.forEach(oldObject, function(value, key) {
                        if (!newObject[key]) {
                            delete oldObject[key];
                        } else if (angular.isArray(newObject[key])) {
                            oldObject[key].length = newObject[key].length;
                        }
                    });
                    angular.forEach(newObject, function(value, key) {
                        if (angular.isArray(newObject[key]) || angular.isObject(newObject[key])) {
                            if (!oldObject[key]) {
                                oldObject[key] = angular.isArray(newObject[key]) ? [] : {};
                            }
                            overrideValues(oldObject[key], newObject[key]);
                        } else {
                            oldObject[key] = newObject[key];
                        }
                    });
                }


                if (promiseCache[localeId]) {
                    activeLocale = localeId;
                    return promiseCache[localeId];
                }

                var cachedLocale,
                    deferred = $q.defer();
                if (localeId === activeLocale) {
                    deferred.resolve($locale);
                } else if ((cachedLocale = localeCache.get(localeId))) {
                    activeLocale = localeId;
                    $rootScope.$evalAsync(function() {
                        overrideValues($locale, cachedLocale);
                        storage.put(storageKey, localeId);
                        $rootScope.$broadcast('$localeChangeSuccess', localeId, $locale);
                        deferred.resolve($locale);
                    });
                } else {
                    activeLocale = localeId;
                    promiseCache[localeId] = deferred.promise;
                    loadScript(localeUrl, function() {
                        // Create a new injector with the new locale
                        var localInjector = angular.injector(['ngLocale']),
                            externalLocale = localInjector.get('$locale');

                        overrideValues($locale, externalLocale);
                        localeCache.put(localeId, externalLocale);
                        delete promiseCache[localeId];

                        $rootScope.$applyAsync(function() {
                            storage.put(storageKey, localeId);
                            $rootScope.$broadcast('$localeChangeSuccess', localeId, $locale);
                            deferred.resolve($locale);
                        });
                    }, function() {
                        delete promiseCache[localeId];

                        $rootScope.$applyAsync(function() {
                            if (activeLocale === localeId) {
                                activeLocale = $locale.id;
                            }
                            $rootScope.$broadcast('$localeChangeError', localeId);
                            deferred.reject(localeId);
                        });
                    }, $timeout);
                }
                return deferred.promise;
            }

            this.localeLocationPattern = function(value) {
                if (value) {
                    localeLocationPattern = value;
                    return this;
                } else {
                    return localeLocationPattern;
                }
            };

            this.appendScriptTo = function(nodeElement) {
                nodeToAppend = nodeElement;
            };

            this.useStorage = function(storageName) {
                storageFactory = storageName;
            };

            this.useCookieStorage = function() {
                this.useStorage('$cookieStore');
            };

            this.defaultLocale = function(value) {
                defaultLocale = value;
            };

            this.storageKey = function(value) {
                if (value) {
                    storageKey = value;
                    return this;
                } else {
                    return storageKey;
                }
            };

            this.addLocalePatternValue = function(key, value) {
                extraProperties[key] = value;
            };

            this.$get = ['$rootScope', '$injector', '$interpolate', '$locale', '$q', 'tmhDynamicLocaleCache', '$timeout', function($rootScope, $injector, interpolate, locale, $q, tmhDynamicLocaleCache, $timeout) {
                var localeLocation = interpolate(localeLocationPattern);

                storage = $injector.get(storageFactory);
                $rootScope.$evalAsync(function() {
                    var initialLocale;
                    if ((initialLocale = (storage.get(storageKey) || defaultLocale))) {
                        loadLocaleFn(initialLocale);
                    }
                });
                return {
                    /**
                     * @ngdoc method
                     * @description
                     * @param {string} value Sets the locale to the new locale. Changing the locale will trigger
                     *    a background task that will retrieve the new locale and configure the current $locale
                     *    instance with the information from the new locale
                     */
                    set: loadLocaleFn,
                    /**
                     * @ngdoc method
                     * @description Returns the configured locale
                     */
                    get: function() {
                        return activeLocale;
                    }
                };

                function loadLocaleFn(localeId) {
                    var baseProperties = {locale: localeId, angularVersion: angular.version.full};
                    return loadLocale(localeLocation(angular.extend({}, extraProperties, baseProperties)), locale, localeId, $rootScope, $q, tmhDynamicLocaleCache, $timeout);
                }
            }];
        }]).provider('tmhDynamicLocaleCache', function() {
            this.$get = ['$cacheFactory', function($cacheFactory) {
                return $cacheFactory('tmh.dynamicLocales');
            }];
        }).provider('tmhDynamicLocaleStorageCache', function() {
            this.$get = ['$cacheFactory', function($cacheFactory) {
                return $cacheFactory('tmh.dynamicLocales.store');
            }];
        }).run(['tmhDynamicLocale', angular.noop]);

    return 'tmh.dynamicLocale';

}));
;/* global
 angular
 */
angular.module('starter').service('$translate', function () {
    var service = {};

    service.translations = [];

    service.instant = function (text) {
        return angular.isDefined(service.translations[text]) ? service.translations[text] : text;
    };

    return service;
});
;/*global
    App, angular, DOMAIN, CURRENT_LANGUAGE, APP_KEY
 */
angular.module("starter").service("Url", function($location) {

    this.__sanitize = function(str) {

        if(str.startsWith("/")) {
            str = str.substr(1, str.length - 1);
        }

        return str;
    };

    var _that = this;

    return {

        get: function(uri, params) {

            if(!angular.isDefined(params)) {
                params = {};
            }

            var add_language = params.add_language;
            delete params.add_language;

            var remove_key = params.remove_key;
            delete params.remove_key;

            uri = _that.__sanitize(uri);

            var url = DOMAIN.split("/");

            if(add_language) {
                url.push(CURRENT_LANGUAGE);
            }

            if(APP_KEY && !remove_key) {
                url.push(APP_KEY);
            }

            url.push(uri);

            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    url.push(i);
                    url.push(params[i]);
                }
            }

            url = url.join('/');

            return url;
        },

        build: function(uri, params) {

            if(!angular.isDefined(params)) {
                params = {};
            }

            var url = _that.__sanitize(uri);
            var p = [];

            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    p.push(i+"="+params[i]);
                }
            }

            url = url + "?" + p.join("&");

            return url;
        }

    };
});

if(typeof String.prototype.startsWith !== "function") {
    String.prototype.startsWith = function (str) {
        return this.substring(0, str.length) === str;
    };
}

if(typeof String.prototype.endsWith !== "function") {
    String.prototype.endsWith = function (str) {
        return this.substring(this.length - str.length, this.length) === str;
    };
};/* global
 angular, YT
 */
angular.module('starter').service('YouTubeAutoPauser', function ($ionicPlatform, $window) {
    var iframes = [];
    var players = [];
    var initialized = false;
    var loaded = false;
    var service = {};

    /**
     *
     */
    function initialize() {
        initialized = true;
        $window.onYouTubeIframeAPIReady = function () {
            loaded = true;
            players = players.concat(iframes.map(function (iframe) {
                return new YT.Player(iframe, {});
            }));

            $ionicPlatform.on('pause', function (result) {
                var filtered_players = [];
                players.forEach(function (item) {
                    if (
                        angular.isObject(item) && // YT.Player!
                        angular.isFunction(item.pauseVideo) && // check function exists!
                        angular.isObject(item.a) && // iframe element!
                        angular.isObject(item.a.parentElement) // check if still in DOM!
                    ) {
                        item.pauseVideo();
                        filtered_players.push(item);
                    }
                });
                players = filtered_players; // replace players with checked and filtered!
            });
        };

        var tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    /**
     *
     * @param iframe
     */
    service.register = function (iframe) {
        var localIframe = angular.element(iframe)[0];

        if (loaded) {
            players.push(new YT.Player(localIframe, {}));
        } else {
            if (!initialized) {
                initialize();
            }
            iframes.push(localIframe);
        }
    };

    return service;
});
