/**
 * Application
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.18
 */
angular.module('starter').service('Application', function ($pwaRequest, $ocLazyLoad, $injector, $q, $rootScope, $session, $timeout, $ionicPlatform,
                                                           $window, $queue, $log, Analytics, Dialog, ProgressbarService, AdmobService) {
angular
    .module('starter')
    .service('Application', function ($pwaRequest, $q, $rootScope, $session, $timeout, $ionicPlatform, $window) {
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
        service.homepage_background = data.application.useHomepageBackground;
        service.backButton = data.application.backButton;
        service.backButtonClass = data.application.backButtonClass;
        service.leftToggleClass = data.application.leftToggleClass;
        service.rightToggleClass = data.application.rightToggleClass;
        service.myAccount = data.application.myAccount;

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
