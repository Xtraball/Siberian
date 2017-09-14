/* global
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
