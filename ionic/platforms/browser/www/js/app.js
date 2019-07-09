/**
 * Application Bootstrap
 *
 * @version 4.16.10
 */

window.momentjs_loaded = false;
window.extractI18n = true;
var DEBUG = false;

// Fallback for non re-published apps
if (IS_NATIVE_APP === undefined) {
    var IS_NATIVE_APP = false;
    if ((cordova !== undefined) && ((cordova.platformId === 'android') || (cordova.platformId === 'ios'))) {
        IS_NATIVE_APP = true;
    }
}
if (DEVICE_TYPE === undefined) {
    var DEVICE_TYPE = 3;
    if (cordova !== undefined) {
        switch (cordova.platformId) {
            case 'android':
                DEVICE_TYPE = 1;
                break;
            case 'ios':
                DEVICE_TYPE = 2;
                break;
            default:
                DEVICE_TYPE = 3;
        }
    }
}
if (LOGIN_FB === undefined) {
    var LOGIN_FB = false;
}
if (IS_PREVIEW === undefined) {
    var IS_PREVIEW = false;
}
// Fallback for non re-published apps
var isNativeApp = IS_NATIVE_APP;
var isOverview = (window.location.href.indexOf('/apps/overview/') !== -1);
var lazyLoadResolver = function (code) {
    return {
        lazy: ['$q', '$timeout', '$ocLazyLoad', function ($q, $timeout, $ocLazyLoad) {
            var localCode = angular.copy(code);
            if (!angular.isArray(localCode)) {
                localCode = [localCode];
            }
            var files = [];
            localCode.forEach(function (file) {
                files.push('./dist/packed/' + file + '.bundle.min.js');
            });

            var deferred = $q.defer();

            $ocLazyLoad
                .load(files)
                .then(function () {
                    $timeout(function () {
                        deferred.resolve(true);
                    }, 1);
                });

            return deferred.promise;
        }]
    };
};

angular.module('lodash', []).factory('_', ['$window', function ($window) {
    return $window._;
}]);

var semver = {compare: function (a, b, specificity) {var pa = a.split('.');var pb = b.split('.');var sentinels = {'major': 1, 'minor': 2, 'patch': 3};for (var i = 0; i < (sentinels[specificity] || 3); i++) {na = Number(pa[i]);nb = Number(pb[i]);if (na > nb || !isNaN(na) && isNaN(nb)) {return 1;}if (na < nb || isNaN(na) && !isNaN(nb)) {return -1;}}return 0;}, isGreater: function (a, b, specificity) {return this.compare(a, b, specificity) === 1;}, isLess: function (a, b, specificity) {return this.compare(a, b, specificity) === -1;}, isEqual: function (a, b, specificity) {return this.compare(a, b, specificity) === 0;}};

var App = angular.module('starter', ['ionic', 'lodash', 'ngRoute', 'ngCordova', 'ngSanitize', 'ngQueue',
    'ion-gallery', 'ngImgCrop', 'ionic-zoom-view', 'tmh.dynamicLocale', 'templates', 'oc.lazyLoad'])
    .constant('$ionicLoadingConfig', { template: '<ion-spinner></ion-spinner>' })
    .constant('SB', {
        EVENTS: {
            AUTH: {
                loginSuccess: 'auth-login-success',
                logoutSuccess: 'auth-logout-success',
                registerSuccess: 'auth-register-success',
                editSuccess: 'auth-edit-success'
            },
            CACHE: {
                pagesReload: 'pages-reload',
                layoutReload: 'layout-reload',
                clearSocialGaming: 'clear-cache-socialgaming',
                clearDiscount: 'clear-cache-discount'
            },
            PADLOCK: {
                unlockFeatures: 'padlock-unlock-features',
                lockFeatures: 'padlock-lock-features'
            },
            PUSH: {
                notificationReceived: 'push-notification-received',
                unreadPush: 'push-get-unreaded',
                readPush: 'push-mark-as-read'
            },
            MEDIA_PLAYER: {
                HIDE: 'media-player-hide',
                SHOW: 'media-player-show'
            }
        },
        DEVICE: {
            TYPE_ANDROID: 1,
            TYPE_IOS: 2,
            TYPE_BROWSER: 3
        }
    })
    // Deprecated constants below, fallback pre 5.0
    .constant('AUTH_EVENTS', { loginSuccess: 'auth-login-success', logoutSuccess: 'auth-logout-success', loginStatusChanged: 'auth-login-status-changed', notAuthenticated: 'auth-not-authenticated' })
    .constant('CACHE_EVENTS', { clearSocialGaming: 'clear-cache-socialgaming', clearDiscount: 'clear-cache-discount' })
    .constant('PADLOCK_EVENTS', { unlockFeatures: 'padlock-unlock-features' })
    .constant('PUSH_EVENTS', { notificationReceived: 'push-notification-received', unreadPushs: 'push-get-unreaded', readPushs: 'push-mark-as-read' })

    // Start app config
    .config(function ($compileProvider, $httpProvider, $ionicConfigProvider, $logProvider) {

        /** Hooks on HTTP transactions */
        $httpProvider.interceptors.push(function ($injector, $log, $q, $session) {
            return {
                request: function (config) {
                    // Append session id if not present!
                    var sessionId = $session.getId();
                    if ((sessionId !== false) && (config.url.indexOf('.html') === -1)) {
                        if ((config.url.indexOf(DOMAIN) > -1) && (config.noSbToken !== true)) {
                            var sessionParam = 'sb-token=' + sessionId;
                            if (config.url.indexOf('?') > 1) {
                                config.url = config.url + '&' + sessionParam;
                            } else {
                                config.url = config.url + '?' + sessionParam;
                            }
                        }
                    }
                    return config;
                },
                responseError: function (response) {
                    // Handle layout errors!
                    if (response.config.url.match(/(templates|layout\/home)\/.*\.html$/) &&
                        (response.config.url !== 'templates/home/l6/view.html')) {
                        $log.debug('System: An error occured while loading your Layout template, fallback on Layout 6.');

                        response.config.url = 'templates/home/l6/view.html';

                        return $injector.get('$pwaRequest')(response.config);
                    }
                    return $q.reject(response);
                }
            };
        });


        $logProvider.debugEnabled(DEBUG);
        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|map|geo|skype|tel|file|smsto):/);
        $httpProvider.defaults.withCredentials = true;
        $ionicConfigProvider.views.swipeBackEnabled(false);
        $ionicConfigProvider.backButton.text('');
        $ionicConfigProvider.backButton.previousTitleText(false);
    })
    .run(function ($injector, $ionicConfig, $ionicHistory, $ionicNavBarDelegate, $ionicPlatform, $ionicPopup,
                   $ionicScrollDelegate, $ionicSlideBoxDelegate, $location, $log, $ocLazyLoad, $pwaRequest, $q,
                   $rootScope, $session, $state, $templateCache, $timeout, $translate, $window, AdmobService,
                   Analytics, Application, Customer, Dialog, Facebook, FacebookConnect, Padlock,
                   Pages, Push, PushService, SB) {

        // $rootScope object!
        angular.extend($rootScope, {
            isNativeApp: IS_NATIVE_APP,
            isOnline: true,
            isOffline: false,
            card_design: false,
            app_is_loaded: true,
            app_is_bo_locked: false,
            ui_background_loader: false,
            ui_progress_view: false,
            loginFeatureBack: true
        });

        // Listeners for network events!
        $window.addEventListener('online', function () {
            $log.info('online');
            $rootScope.isOnline = true;
            $rootScope.isOffline = false;
        });

        $window.addEventListener('offline', function () {
            $log.info('offline');
            $rootScope.isOnline = false;
            $rootScope.isOffline = true;
        });

        $rootScope.openLoaderProgress = function () {
            $rootScope.ui_background_loader = false;
            $rootScope.ui_progress_view = true;
        };

        $rootScope.closeLoaderProgress = function () {
            $rootScope.ui_background_loader = false;
            $rootScope.ui_progress_view = false;
        };

        $rootScope.backgroundLoaderProgress = function () {
            $rootScope.ui_background_loader = true;
            $rootScope.ui_progress_view = false;
        };

        // @note should be used the less possible!
        $rootScope.isNotAvailableOffline = function () {
            if ($rootScope.isOffline) {
                Dialog.alert('Offline mode',
                    'This feature is not available in offline mode!', 'Dismiss', 2350);

                return true;
            }
            return false;
        };

        $rootScope.isNotAvailableInOverview = function () {
            if (isOverview) {
                Dialog.alert('Overview',
                    'This feature is disabled in this preview', 'Dismiss', -1);

                return true;
            }
            return false;
        };

        $ionicPlatform.ready(function () {
            $ionicNavBarDelegate.align('center');
            $timeout(function () {
                $ionicNavBarDelegate.showBar(false);
            });

            // Display previewer notice!
            if (IS_PREVIEW) {
                $rootScope.previewerNotice = true;
            }

            var loadApp = function (refresh) {
                // Fallback empty objects for browser!
                $window.cordova = $window.cordova || {};
                $window.device = $window.device || {};

                var networkPromise = $q.defer();

                // Session is ready we can initiate first request!
                $session.loaded.then(function () {
                    var deviceScreen = $session.getDeviceScreen();

                    $pwaRequest.post('front/app/init', {
                        data: {
                            add_language: true,
                            device_uid: $session.getDeviceUid(),
                            device_width: deviceScreen.width,
                            device_height: deviceScreen.height,
                            version: "4.17.1"
                        },
                        timeout: 20000,
                        cache: !isOverview,
                        refresh: refresh,
                        network_promise: networkPromise
                    }).then(function (data) {
                        var load = data.loadBlock;
                        var manifest = data.manifestBlock;

                        // Translations & locale!
                        $translate.translations = data.translationBlock;

                        if (!$session.getId()) {
                            $session.setId(data.loadBlock.customer.token);
                        }

                        // Populate main objects!
                        Application.populate(data.loadBlock);

                        // Overrides backbutton icon
                        $ionicConfig.backButton.icon(Application.getBackIcon());

                        Customer.populate(data.loadBlock.customer);
                        Customer.setFacebookLogin(data.loadBlock.application.facebook);
                        Pages.populate(data.featureBlock);

                        // Login Facebook HTML5!
                        if (LOGIN_FB) {
                            Customer.loginWithFacebook(fbtoken);
                        }

                        var HomepageLayout = $injector.get("HomepageLayout");

                        // Append custom CSS/SCSS to the page!
                        if (data.cssBlock && data.cssBlock.css) {
                            var css = document.createElement("style");
                            css.type = "text/css";
                            css.innerHTML = data.cssBlock.css;
                            document.body.appendChild(css);
                        }

                        // Web apps manifest!
                        if (!$rootScope.isOverview && !$rootScope.isNativeApp) {
                            var head = angular.element(document.querySelector('head'));

                            if (manifest.iconUrl) {
                                head.append('<link rel="apple-touch-icon" href="' + manifest.iconUrl + '" />');
                                head.append('<link rel="icon" sizes="192x192" href="' + manifest.iconUrl + '" />');
                            }

                            if (manifest.manifestUrl) {
                                head.append('<link rel="manifest" href="' + DOMAIN + manifest.manifestUrl + '">');
                            }

                            if (manifest.startupImageUrl) {
                                head.append('<link rel="apple-touch-startup-image" href="' + manifest.startupImageUrl + '" />');
                            }

                            if (manifest.themeColor) {
                                head.append('<meta name="theme-color" content="' + manifest.themeColor + '" />');
                            }
                        }

                        // App keyboard & StatusBar!
                        if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
                            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(false);
                        }

                        // Configuring PushService & skip if this is a preview.
                        if (!IS_PREVIEW) {
                            try {
                                PushService.configure(load.application.fcmSenderID, load.application.pushIconcolor);
                                PushService.register();
                            } catch (e) {
                                $log.error('An error occured while registering device for Push.', e.message);
                            }
                        }

                        // skip chcp inside webview loaded app!
                        if (!IS_PREVIEW) {
                            $rootScope.fetchupdatetimer = null;

                            $ionicPlatform.on('resume', function (resumeResult) {
                                // If app goes live too fast, cancel the update.
                                $timeout.cancel($rootScope.fetchupdatetimer);

                                $log.info('-- app is resumed --');
                                Analytics.storeOpening().then(function (result) {
                                    Analytics.data.storeClosingId = result.id;
                                });

                                $rootScope.onPause = false;
                            });

                            $rootScope.onPause = false;
                            $ionicPlatform.on('pause', function (pauseResult) {
                                $rootScope.onPause = true;
                                Analytics.storeClosing();

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
                        }
                        // !skip chcp inside webview loaded app!

                        if (load.application.is_bo_locked) {
                            $rootScope.app_is_bo_locked = true;

                            $ionicHistory.nextViewOptions({
                                disableBack: true
                            });

                            $state.go("locked");
                        }

                        if (window.StatusBar !== undefined) {
                            switch (DEVICE_TYPE) {
                                case SB.DEVICE.TYPE_ANDROID:
                                    if (load.application.androidStatusBarIsHidden === true) {
                                        window.StatusBar.hide();
                                    }
                                    break;
                                case SB.DEVICE.TYPE_IOS:
                                    if (load.application.iosStatusBarIsHidden === true) {
                                        window.StatusBar.hide();
                                    }
                                    break;
                                default:
                                    // Do nothing!
                            }
                        }


                        if ($rootScope.isNativeApp) {
                            if (!$window.localStorage.getItem("first_running")) {
                                $window.localStorage.setItem("first_running", "true");
                                Analytics.storeInstallation();
                            }

                            if (Application.offline_content) {
                                Application.showCacheDownloadModalOrUpdate();
                            }
                        }

                        // not the best place.
                        Analytics.storeOpening()
                            .then(function (result) {
                                if (result && result.id) {
                                    Analytics.data.storeClosingId = result.id;
                                }
                            });

                        $rootScope.app_is_locked = Application.is_locked &&
                            !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode);

                        $window.colors = load.application.colors;
                        if (window.StatusBar !== undefined) {
                            window.updateStatusBar($window.colors.header.statusBarColor);
                        }

                        if (load.application.facebook.id) {
                            FacebookConnect.permissions = (!Array.isArray(load.application.facebook.scope)) ?
                                [load.application.facebook.scope] : load.application.facebook.scope;
                            FacebookConnect.app_id = load.application.facebook.id;
                        }

                        try {
                            AdmobService.init(load.application.admob);
                        } catch (error) {
                            $log.error('Unable to initialize AdMob.', error);
                        }

                        if (Customer.isLoggedIn()) {
                            $rootScope.$broadcast(SB.EVENTS.AUTH.loginSuccess);
                        } else {
                            $rootScope.$broadcast(SB.EVENTS.AUTH.logoutSuccess);
                        }

                        // cyril: RIDICULOUS CODE, in browser we use _system that is not accepted value!
                        // in application we open with _blank that open with inAppBrowser without control...!
                        $rootScope.getTargetForLink = function () {
                            return !$rootScope.isNativeApp ? '_system' : '_blank';
                        };

                        $ionicNavBarDelegate.align('center');
                        $timeout(function () {
                            $ionicNavBarDelegate.showBar(false);
                        });

                        $rootScope.$on('$ionicView.loaded', function (event, loadViewData) {
                            if (loadViewData.stateName !== 'home') {
                                $timeout(function () {
                                    $ionicNavBarDelegate.showBar(true);
                                }, 100);
                            } else {
                                $timeout(function () {
                                    $ionicNavBarDelegate.showBar(!!HomepageLayout.properties.options.autoSelectFirst);
                                }, 100);
                            }
                        });

                        // Handler for overview & navbar!
                        $rootScope.$on('$stateChangeSuccess', function (event, toState, toStateParams, fromState, fromStateParams) {
                            // Only for overview.
                            if (parent && (typeof parent.postMessage === 'function') && (parent !== window)) {
                                parent.postMessage('state.go', DOMAIN);
                            }
                        });

                        $rootScope.$on('$stateChangeStart', function (event, toState, toStateParams, fromState, fromStateParams) {
                            $rootScope.app_is_locked = Application.is_locked &&
                                !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode);

                            if ($rootScope.app_is_locked && (toState.name !== 'padlock-view')) {
                                event.preventDefault();

                                $state.go('padlock-view');
                            } else if (Customer.can_access_locked_features && (toState.name === 'padlock-view')) {
                                event.preventDefault();
                            } else if ((toState.name === 'codescan') && $rootScope.isNotAvailableInOverview()) {
                                event.preventDefault();
                            }
                        });

                        // Event to catch state-go from source code!
                        var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
                        var eventer = window[eventMethod];
                        var messageEvent = (eventMethod === 'attachEvent') ? 'onmessage' : 'message';

                        // Listen to message from child window
                        eventer(messageEvent, function (e) {
                            var parts = e.data.split('=');
                            var action = parts[0];
                            var params = {};
                            if (parts.length >= 2) {
                                action = parts[0];
                                params = parts[1].replace(/(^\?)/,'').split(',').map(function (n){return n = n.split(':'),this[n[0].trim()] = n[1],this}.bind({}))[0];
                            }

                            var offline = (typeof params.offline !== 'undefined') ?
                                (params.offline === 'true') : false;

                            // Special in-app link for my account!
                            if (params.state === "my-account") {
                                Customer.loginModal();
                                return;
                            }

                            switch (action) {
                                case "state-go":
                                    if (params.hasOwnProperty("value_id")) {
                                        var feature = Pages.getValueId(params.value_id);
                                        if (feature && !feature.is_active) {
                                            Dialog.alert("Error", "This feature is no longer available.", "OK", 2350);
                                            return;
                                        }
                                    }

                                    var state = params.state;
                                    delete params.state;
                                    delete params.offline;
                                    if (!offline && $rootScope.isNotAvailableOffline()) {
                                        return;
                                    }
                                    $state.go(state, params);
                                    break;
                                default:
                                    // Nope!
                            }
                        }, false);

                        // Global listeners for logout/lock app!
                        $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
                            $rootScope.app_is_locked = (Application.is_locked &&
                                !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode));

                            if (!$rootScope.app_is_locked && Application.is_locked) {
                                $state.go('home');
                            }
                        });

                        $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
                            $rootScope.app_is_locked = (Application.is_locked && !Padlock.unlocked_by_qr_code);

                            if ($rootScope.app_is_locked) {
                                $ionicHistory.nextViewOptions({
                                    disableBack: true
                                });
                                $state.go('padlock-view');
                            }
                        });

                        $rootScope.$on('$ionicView.beforeEnter', function () {
                            Analytics.storeClosing();
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

                        // OVERVIEW!
                        $rootScope.isOverview = isOverview;
                        if ($rootScope.isOverview) {
                            $window.overview = {
                                features: {}
                            };

                            $window.isHomepage = function () {
                                return ($location.path() === BASE_PATH);
                            };

                            $window.clearCache = function (url) {
                                $templateCache.remove(BASE_PATH + '/' + url);
                            };

                            $window.reload = function (path) {
                                if (!path || (path === $location.path())) {
                                    $ionicHistory.clearCache();
                                    $state.reload();
                                }
                            };

                            $window.reloadTabbar = function () {
                                HomepageLayout.unsetData();
                            };

                            $window.setPath = function (path, replace) {
                                if ($window.isSamePath(path)) {
                                    $window.reload();
                                } else if (path.length) {
                                    $timeout(function () {
                                        $location.path(path);
                                        if (replace) {
                                            $location.replace();
                                        }
                                    });
                                }
                            };

                            $window.getPath = function () {
                                return $location.path();
                            };

                            $window.isSamePath = function (path) {
                                return ($location.path() === path);
                            };

                            $window.showHomepage = function () {

                                if (HomepageLayout.properties.menu.visibility === 'homepage') {
                                    $window.setPath(BASE_PATH);
                                } else {
                                    HomepageLayout.getFeatures().then(function (features) {
                                        $ionicHistory.nextViewOptions({
                                            historyRoot: true,
                                            disableAnimate: false
                                        });
                                        var featIndex = 0;

                                        // We show only `visible options`
                                        var visibleOptions = features.options.filter(function (option) {
                                            return option.is_visible;
                                        });

                                        for (var fi = 0; fi < visibleOptions.length; fi = fi + 1) {
                                            var feat = visibleOptions[fi];

                                            // Don't load unwanted features on first page.!
                                            if (["code_scan", "radio", "padlock", "tabbar_account"].indexOf(feat.code) >= 0) {
                                                featIndex = fi;
                                                break;
                                            }
                                        }

                                        if (visibleOptions[fi]) {
                                            $window.setPath(visibleOptions[fi].path, true);
                                        }
                                    });
                                }
                            };

                            $window.back = function () {
                                // If go back is home!
                                $ionicHistory.goBack();
                            };

                            $window.setLayoutId = function (valueId, layoutId) {
                                HomepageLayout.setLayoutId(valueId, layoutId);
                            };

                            if (parent && (typeof parent.postMessage === 'function') && (parent !== window)) {
                                parent.postMessage('overview.loaded', DOMAIN);
                            }
                        }

                        /**
                         * Fallback methods, proxy
                         *
                         * @deprecated
                         * @type {*}
                         */
                        $rootScope.onlineOnly = $rootScope.isNotAvailableOffline;
                        $rootScope.showMobileFeatureOnlyError = $rootScope.isNotAvailableInOverview;
                        /**
                         * Trash previous when done.
                         */

                        Application.loaded = true;

                        networkPromise.promise
                            .then(function (networkPromiseResult) {
                                // On refresh cache success, refresh pages, then refresh homepage!
                                Pages.populate(networkPromiseResult.featureBlock);
                                $rootScope.$broadcast(SB.EVENTS.CACHE.layoutReload);
                            }, function () {})
                            .then(function () {
                                // Pre-load states!
                                $timeout(function () {
                                    Application.preLoad(Pages.data.pages);
                                }, 100);
                            });

                        // Loads momentjs/progressbar async.
                        $ocLazyLoad.load("./dist/lazy/moment.min.js")
                            .then(function () {
                                window.momentjs_loaded = true;
                                try {
                                    var tmpLang = language.replace("_", "-").toLowerCase();
                                    moment.locale([tmpLang, "en"]);
                                } catch (e) {
                                    moment.locale("en");
                                }

                                console.log("moment locale", moment.locale());
                            });

                        $ocLazyLoad.load('./dist/lazy/angular-carousel.min.js');
                        window.Features.featuresToLoadOnStart.forEach(function (bundle) {
                            $log.info('Loading on Start: ', bundle);
                            $ocLazyLoad.load([
                                bundle.path
                            ], {
                                cache: false
                            }).then(function (success) {
                                // Call onRun action
                                if ($injector.has(bundle.factory)) {
                                    try {
                                        $injector.get(bundle.factory).onStart();
                                    } catch (e) {
                                        // Unable to find/start onStart();
                                    }
                                }
                            }).catch(function (error) {
                            });
                        });

                        var ProgressbarService = $injector.get('ProgressbarService');
                        ProgressbarService.init(load.application.colors.loader);

                        // Check for padlock!
                        var currentState = $ionicHistory.currentStateName();
                        if ($rootScope.app_is_locked && (currentState !== 'padlock-view')) {
                            $state.go('padlock-view');
                        }

                        // When App is loaded dismiss the previewerNotice!
                        if (IS_PREVIEW) {
                            $timeout(function () {
                                $rootScope.previewerNotice = false;
                            }, 3000);

                            $window.registerTap(3, function () {
                                $window.webview.close();
                            });
                        }
                    }).catch(function (error) {
                        // In case we are unable to refresh loadApp, use cached version and refresh only once
                        if (refresh === true) {
                            $timeout(loadApp(false), 1);
                        }
                    }); // Main load, then
                }); // Session loaded
            };

            $timeout(loadApp(true), 1);
        });
    });