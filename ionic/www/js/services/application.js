/* global
    App, caches, cacheName, ionic, DOMAIN, _, window, localStorage, IS_NATIVE_APP
*/

/**
 * Application
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('Application', function ($pwaRequest, $ocLazyLoad, $injector, $q, $rootScope, $session, $timeout, $ionicPlatform,
                                                           $window, $queue, $log, Analytics, Dialog, ProgressbarService, AdmobService) {
    var service = {
        is_webview: !IS_NATIVE_APP,
        _rawData: {},
        is_customizing_colors: ($window.location.href.indexOf('application/mobile_customization_colors/') >= 0)
    };

    var _loaded = false;
    var _loaded_resolver = $q.defer();
    var _ready = false;
    var _ready_resolver = $q.defer();

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

    /**
     * Helper to defer loading deps after app is ready!
     */
    service.deferDeps = function () {
        $timeout(function () {
            service.initProgressBar();
            service.loadMomentJs();
            service.analyticsStart();
            service.chcpListener();
            service.initPush();
            service.initAdmob();
        }, 1000);
    };

    service.initProgressBar = function () {
        try {
            ProgressbarService.init(service._rawData.application.colors.loader);
        } catch (error) {
            $log.error('Unable to initialize AdMob.', error);
        }
    };

    service.loadMomentJs = function () {
        // Loads MomentJS async.
        $ocLazyLoad
        .load("./dist/lazy/moment.min.js")
        .then(function () {
            window.momentjs_loaded = true;
            try {
                var tmpLang = language.replace("_", "-").toLowerCase();
                moment.locale([tmpLang, "en"]);
            } catch (e) {
                moment.locale("en");
            }
        });
    };

    service.initPush = function () {
        if (IS_PREVIEW) {
            return;
        }
        // Then AdMob
        $timeout(function () {
            // Configuring PushService & skip if this is a preview.
            try {
                $ocLazyLoad
                    .load("./features/push_notification/push_notification.js")
                    .then(function () {
                        var PushService = $injector.get("PushService");
                        PushService.configure(
                            service._rawData.application.fcmSenderID,
                            service._rawData.application.pushIconcolor,
                            service.app_id,
                            service.app_name);
                        PushService.register();
                    });
            } catch (e) {
                $log.error('An error occured while registering device for Push.', e.message);
            }
        }, 1000);
    };

    service.initAdmob = function () {
        // Then AdMob
        $timeout(function () {
            try {
                AdmobService.init(service._rawData.application.admob);
            } catch (error) {
                $log.error('Unable to initialize AdMob.', error);
            }
        }, 2000);
    };

    service.analyticsStart = function () {
        // Register app install
        if (isNativeApp && !$window.localStorage.getItem("first_running")) {
            $window.localStorage.setItem("first_running", "true");
            Analytics.storeInstallation();
        }

        // Register app start
        Analytics.storeOpening()
        .then(function (result) {
            if (result && result.id) {
                Analytics.data.storeClosingId = result.id;
            }
        });
    };

    service.chcpListener = function () {
        if (IS_PREVIEW) {
            return;
        }

        $rootScope.fetchupdatetimer = null;

        $ionicPlatform.on('resume', function (resumeResult) {
            // If app goes live too fast, cancel the update.
            $timeout.cancel($rootScope.fetchupdatetimer);
            $rootScope.onPause = false;
        });

        $rootScope.onPause = false;
        $ionicPlatform.on('pause', function (pauseResult) {
            $rootScope.onPause = true;

            var runChcp = function () {
                // When app goes in pause, try to install if required.
                if (typeof chcp !== 'undefined') {
                    $rootScope.fetchupdatetimer = $timeout(function () {
                        if (localStorage.getItem('install-update' === true)) {
                            chcp.isUpdateAvailableForInstallation(function (error, data) {
                                if (error) {
                                    $log.info('CHCP: Nothing to install');
                                    $log.info('CHCP: ' + error.description);
                                    return;
                                }

                                // update is in cache and can be installed - install it
                                $log.info('CHCP: Current version: ' + data.currentVersion);
                                $log.info('CHCP: About to install: ' + data.readyToInstallVersion);
                                chcp.installUpdate(function (error) {
                                    if (error) {
                                        $log.info('CHCP: Something went wrong with the update, will retry later.');
                                        $log.info('CHCP: ' + error.description);
                                    } else {
                                        return;
                                    }
                                });
                            });
                        } else {
                            chcp.fetchUpdate(function (error, data) {
                                if (error) {
                                    if (error.code === 2) {
                                        $log.info('CHCP: There is no available update.');
                                    } else {
                                        $log.info('CHCP: Failed to load the update with error code: ' + error.code);
                                    }

                                    $log.info('CHCP: ' + error.description);
                                    localStorage.setItem('install-update', false);
                                } else {
                                    $log.info('CHCP: Update success, trying to install.');

                                    // update is in cache and can be installed - install it
                                    $log.info('CHCP: Current version: ' + data.currentVersion);
                                    $log.info('CHCP: About to install: ' + data.readyToInstallVersion);
                                    chcp.installUpdate(function (error) {
                                        if (error) {
                                            $log.info('CHCP: Something went wrong with the update, will retry later.');
                                            $log.info('CHCP: ' + error.description);
                                        } else {
                                            $log.info('CHCP: Update successfully install, restarting new files.');
                                            localStorage.setItem('install-update', false);
                                            return;
                                        }
                                    });
                                }
                            });
                        }
                    }, 5000);
                }
            };

            // Ensure we won't update an app while the previewer is in progress!
            window.fileExists(
                'module.js',
                function () {
                    // do nothing when file exists!
                }, function () {
                    // run update if file doesn't exists!
                    runChcp();
                });
        });

        // Debug/Support method to check for updates!
        $rootScope.unlockUpdate = 0;
        $rootScope.checkForUpdate = function () {
            if (!$rootScope.isNativeApp) {
                $log.info('Stop update, Android or iOS is required.');
                return;
            }

            if (IS_PREVIEW) {
                $log.info('Stop update, This an App preview.');
                return;
            }

            if ($rootScope.unlockUpdate < 5) {
                $rootScope.unlockUpdate = $rootScope.unlockUpdate + 1;
                return;
            }

            $rootScope.unlockUpdate = 0;

            Dialog.alert('CHCP', 'Checking for update ...', 'OK', -1);

            chcp.fetchUpdate(function (fetchUpdateError, fetchUpdateData) {
                if (fetchUpdateError) {
                    $log.info('CHCP: Failed to load the update with error code: ' + fetchUpdateError.code);
                    if (fetchUpdateError.code === 2) {
                        Dialog.alert('CHCP', 'There is no available update.', 'Dismiss', -1);
                    } else {
                        Dialog.alert('CHCP', fetchUpdateError.description, 'Dismiss', -1);
                    }
                } else {
                    Dialog.alert('CHCP', 'Successfully downloaded update, installing...', 'Dismiss', -1)
                    .then(function () {
                        // update is in cache and can be installed - install it
                        $log.info('CHCP: Current version: ' + fetchUpdateData.currentVersion);
                        $log.info('CHCP: About to install: ' + fetchUpdateData.readyToInstallVersion);
                        chcp.installUpdate(function (installUpdateError) {
                            if (installUpdateError) {
                                $log.info('CHCP: Something went wrong with the update, will retry later.', -1);
                                Dialog.alert('CHCP', installUpdateError.description, 'Dismiss');
                            } else {
                                Dialog.alert('CHCP', 'Update successfully installed, restarting new files.', 'Dismiss', -1);
                                return;
                            }
                        });
                    });
                }
            });
        };

    };

    /**
     * Populate Application service on load
     *
     * @param data
     */
    service.populate = function (data) {
        // Save a copy of raw data.
        service._rawData = angular.copy(data);

        // Shortcuts
        service.app_id = data.application.id;
        service.app_name = data.application.name;
        service.privacyPolicy = data.application.privacyPolicy;
        service.gdpr = data.application.gdpr;
        service.googlemaps_key = data.application.gmapsKey;
        service.is_locked = data.application.is_locked;
        service.offline_content = data.application.offlineContent;
        service.homepage_background = data.application.useHomepageBackground;
        service.backButton = data.application.backButton;
        service.myAccount = data.application.myAccount;

        // Small base64 default image, while loading the real deal!
        service.default_background = data.homepageImage;
        service.colors = data.application.colors;

        service.ready = true;
    };

    /**
     * @returns {string}
     */
    service.getBackIcon = function () {
        if (service.backButton !== undefined) {
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

<<<<<<< HEAD
=======
    service.showCacheDownloadModalOrUpdate = function () {
        // Lazy Load progressbar, then dooooo it!
        ProgressbarService
            .init()
            .then(function () {
                $rootScope.progressBarPercent = 0;

                var offlineResponse = $window.localStorage.getItem('sb-offline-mode');

                if (offlineResponse === 'ok') {
                    $log.debug('offline mode has been accepted, updating');

                    // Starting a full fetch only after at least 1 minute to prevent network bottleneck!
                    $timeout(function () {
                        service.updateCache(false);
                    }, 60000);
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

                            // Automatically hides offline loader after 4.7 seconds!
                            $timeout(function () {
                                $rootScope.backgroundLoaderProgress();
                            }, 4700);

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
                    pathQueue.pause();
                }
            };

            var options = {
                delay: delay,
                paused: true,
                complete: function () {
                    updateProgress();
                    retry = false;
                    var _retryQueue = retryQueue;
                    if (retryQueue.length > 0) {
                        pathQueue = $queue.queue(fetchAssets, {
                            delay: 1000,
                            paused: true,
                            complete: function () {
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

>>>>>>> hotfix/4.17.6
    return service;
});
