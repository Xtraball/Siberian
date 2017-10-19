/**
 * Application Bootstrap
 */

window.momentjs_loaded = false;
var DEBUG = true;

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
                files.push('./js/packed/' + file + '.bundle.min.js');
            });

            var deferred = $q.defer();

            $ocLazyLoad.load(files)
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
                registerSuccess: 'auth-register-success'
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
    .config(function ($compileProvider, $httpProvider, $ionicConfigProvider, $logProvider, $provide,
                      $pwaRequestProvider, UrlProvider, tmhDynamicLocaleProvider) {
        var Url = UrlProvider.$get();
        var locale_url = Url.get('/app/sae/modules/Application/resources/angular-i18n/angular-locale_{{locale}}.js', {
            remove_key: true
        });

        tmhDynamicLocaleProvider.localeLocationPattern(locale_url);
        tmhDynamicLocaleProvider.storageKey((+new Date()) * Math.random() + ''); // don't remember locale

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
        $ionicConfigProvider.views.maxCache(0);
        $ionicConfigProvider.backButton.previousTitleText(false);
    })
    .run(function ($injector, $ionicConfig, $ionicHistory, $ionicNavBarDelegate, $ionicPlatform, $ionicPopup,
                   $ionicScrollDelegate, $ionicSlideBoxDelegate, $location, $log, $ocLazyLoad, $pwaRequest, $q,
                   $rootScope, $session, $state, $templateCache, $timeout, $translate, $window, AdmobService,
                   Analytics, Application, ConnectionService, Customer, Dialog, Facebook, FacebookConnect, Padlock,
                   Pages, Push, PushService, SB, SafePopups, tmhDynamicLocale) {
        $log.debug('run start');

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
                    'This feature is disabled in the overview', 'Dismiss', -1);

                return true;
            }
            return false;
        };

        $ionicPlatform.ready(function () {
            $ionicNavBarDelegate.showBar(false);

            var loadApp = function (refresh) {
                $log.debug('$ionicPlatform.ready');

                // Fallback empty objects for browser!
                $window.cordova = $window.cordova || {};
                $window.device = $window.device || {};
                $window.ConnectionService = ConnectionService;

                var networkPromise = $q.defer();

                // Session is ready we can initiate first request!
                $session.loaded.then(function () {
                    var deviceScreen = $session.getDeviceScreen();

                    $log.debug('device_uid', $session.getDeviceUid());
                    $log.debug('start: front/mobile/loadv3');

                    $pwaRequest.post('front/mobile/loadv3', {
                        data: {
                            add_language: true,
                            device_uid: $session.getDeviceUid(),
                            device_width: deviceScreen.width,
                            device_height: deviceScreen.height
                        },
                        timeout: 20000,
                        cache: !isOverview,
                        refresh: refresh,
                        network_promise: networkPromise
                    }).then(function (data) {
                        var load = data.load;
                        var manifest = data.manifest;

                        // Translations & locale!
                        $translate.translations = data.translation;
                        tmhDynamicLocale.set($translate.translations._locale);

                        if (!$session.getId()) {
                            $session.setId(data.load.customer.token);
                        }

                        // Populate main objects!
                        Application.populate(data.load);
                        Customer.populate(data.load.customer);
                        Customer.setFacebookLogin(data.load.application.facebook);
                        Pages.populate(data.homepage);

                        // Login Facebook HTML5!
                        if (LOGIN_FB) {
                            Customer.loginWithFacebook(fbtoken);
                        }

                        var HomepageLayout = $injector.get('HomepageLayout');

                        // Append custom CSS/SCSS to the page!
                        if (data.css && data.css.css) {
                            var css = document.createElement('style');
                            css.type = 'text/css';
                            css.innerHTML = data.css.css;
                            document.body.appendChild(css);
                        }

                        // Web apps manifest!
                        if (!$rootScope.isOverview && !$rootScope.isNativeApp) {
                            var head = angular.element(document.querySelector('head'));

                            if (manifest.icon_url) {
                                head.append('<link rel="apple-touch-icon" href="' + manifest.icon_url + '" />');
                                head.append('<link rel="icon" sizes="192x192" href="' + manifest.icon_url + '" />');
                            }

                            if (manifest.manifest_url) {
                                head.append('<link rel="manifest" href="' + DOMAIN + manifest.manifest_url + '">');
                            }

                            if (manifest.startup_image_url) {
                                head.append('<link rel="apple-touch-startup-image" href="' + manifest.startup_image_url + '" />');
                            }

                            if (manifest.theme_color) {
                                head.append('<meta name="theme-color" content="' + manifest.theme_color + '" />');
                            }
                        }

                        // App keyboard & StatusBar!
                        if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
                            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(false);
                        }

                        if (window.StatusBar) {
                            StatusBar.styleDefault();
                        }

                        // Configuring PushService
                        try {
                            PushService.configure(load.application.gcm_senderid, load.application.gcm_iconcolor);
                            PushService.register();
                        } catch (e) {
                            $log.error('An error occured while registering device for Push.', e.message);
                        }


                        $rootScope.fetchupdatetimer = null;

                        $ionicPlatform.on('resume', function (result) {
                            // If app goes live too fast, cancel the update.
                            $timeout.cancel($rootScope.fetchupdatetimer);

                            $log.info('-- app is resumed --');
                            Analytics.storeOpening().then(function (result) {
                                Analytics.data.storeClosingId = result.id;
                            });

                            $rootScope.onPause = false;
                        });

                        $rootScope.onPause = false;
                        $ionicPlatform.on('pause', function (result) {
                            $rootScope.onPause = true;
                            $log.info('-- app is on pause --');
                            Analytics.storeClosing();

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
                        });

                        if (load.application.is_bo_locked) {
                            $rootScope.app_is_bo_locked = true;

                            $ionicHistory.nextViewOptions({
                                disableBack: true
                            });

                            $state.go('locked');
                        }

                        if (window.StatusBar !== undefined) {
                            switch (DEVICE_TYPE) {
                                case SB.DEVICE.TYPE_ANDROID:
                                    if (load.application.android_status_bar_is_hidden === true) {
                                        window.StatusBar.hide();
                                    }
                                    break;
                                case SB.DEVICE.TYPE_IOS:
                                    if (load.application.ios_status_bar_is_hidden === true) {
                                        window.StatusBar.hide();
                                    }
                                    break;
                            }
                        }


                        if ($rootScope.isNativeApp) {
                            if (!$window.localStorage.getItem('first_running')) {
                                $window.localStorage.setItem('first_running', 'true');
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

                        $rootScope.app_is_locked = Application.is_locked && !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode);

                        $window.colors = load.application.colors;

                        if (load.application.facebook.id) {
                            FacebookConnect.permissions = (!Array.isArray(load.application.facebook.scope)) ?
                                new Array(load.application.facebook.scope) : load.application.facebook.scope;
                            FacebookConnect.app_id = load.application.facebook.id;
                        }

                        try {
                            AdmobService.init(load.application.admob_v2);
                        } catch (error) {
                            $log.error('Unable to init AdMob.');
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

                        $window.__ionicNavBarDelegate = $ionicNavBarDelegate;

                        $rootScope.$on('$ionicView.loaded', function (event, data) {
                            if (data.stateName !== 'home') {
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

                            var offline = (typeof params.offline !== 'undefined') ? (params.offline === 'true') : false;

                            switch (action) {
                                case 'state-go':
                                    var state = params.state;
                                    delete params.state;
                                    delete params.offline;
                                    if (!offline && $rootScope.isNotAvailableOffline()) {
                                        return;
                                    }
                                    $state.go(state, params);
                                    break;
                            }
                        }, false);

                        // Global listeners for logout/lock app!
                        $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
                            $rootScope.app_is_locked = (Application.is_locked && !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode));

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

                            if ($rootScope.unlockUpdate < 5) {
                                $rootScope.unlockUpdate = $rootScope.unlockUpdate + 1;
                                return;
                            }

                            $rootScope.unlockUpdate = 0;

                            var checkingUpdate = Dialog.alert('CHCP', 'Checking for update ...', 'OK', -1);

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
                                        for (var fi = 0; fi < features.options.length; fi = fi + 1) {
                                            var feat = features.options[fi];
                                            // Don't load unwanted features on first page.!
                                            if ((feat.code !== 'code_scan') && (feat.code !== 'radio') && (feat.code !== 'padlock')) {
                                                featIndex = fi;
                                                break;
                                            }
                                        }

                                        if (features.options[fi]) {
                                            $window.setPath(features.options[fi].path, true);
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
                                Pages.populate(networkPromiseResult.homepage);
                                $rootScope.$broadcast(SB.EVENTS.CACHE.layoutReload);
                            }, function () {})
                            .then(function () {
                                // Pre-load states!
                                $timeout(function () {
                                    Application.preLoad(Pages.data.pages);
                                }, 100);
                            });

                        // Loads momentjs/progressbar async.
                        $ocLazyLoad.load('./js/libraries/moment.min.js')
                            .then(function () {
                                window.momentjs_loaded = true;
                                try {
                                    moment.locale([language, 'en']);
                                } catch (e) {
                                    moment.locale('en');
                                }
                            });

                        $ocLazyLoad.load('./js/libraries/angular-carousel.min.js');

                        var ProgressbarService = $injector.get('ProgressbarService');
                        ProgressbarService.init(load.application.colors.loader);

                        // Delay background location!
                        $timeout(function () {
                            PushService.startBackgroundGeolocation();
                        }, 5000);

                        $log.debug((new Date()).getTime(), 'end.');

                        // Check for padlock!
                        var currentState = $ionicHistory.currentStateName();
                        if ($rootScope.app_is_locked && (currentState !== 'padlock-view')) {
                            $state.go('padlock-view');
                        }
                    }).catch(function (error) {
                        $log.error('main promise caught error, ', error);

                        // In case we are unable to refresh loadApp, use cached version and refresh only once
                        if (refresh === true) {
                            $timeout(loadApp(false), 1);
                        } else {
                            $log.error('main promise caught error, refresh: false failed.', error);
                        }
                    }); // Main load, then
                }); // Session loaded
            };

            $timeout(loadApp(true), 1);
        });
    });

