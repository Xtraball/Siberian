/**
 * Application Bootstrap
 *
 * @version 4.17.0
 */

window.momentjs_loaded = false;
window.extractI18n = false;
var DEBUG = false;

// Overview & LazyLoader
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

var App = angular.module('starter', ['ionic', 'lodash', 'ngRoute', 'ngSanitize', 'ngQueue',
    'ion-gallery', 'ngImgCrop', 'ionic-zoom-view', 'templates', 'oc.lazyLoad'])
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

    // Start app config
    .config(function ($compileProvider, $httpProvider, $ionicConfigProvider, $logProvider, $stateProvider, $urlRouterProvider) {
        // Add sb-token to every request
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
        $ionicConfigProvider.backButton.text("");
        $ionicConfigProvider.backButton.previousTitleText(false);

        $urlRouterProvider.otherwise(function ($injector, $ocLazyLoad) {
            // Try to load the corresponding module, otherwise, fallback on home

            console.log("$urlRouterProvider.otherwise", BASE_PATH);
            return BASE_PATH;
        });

        // Register lazyModules states
        window.Features.registry.forEach(function (feature) {
            window.Features.createStates($stateProvider, feature.json, feature.bundle);
        });

        window.pwaHtml5="#";
    })
    .run(function ($injector, $ionicConfig, $ionicHistory, $ionicNavBarDelegate, $ionicPlatform, $ionicPopup,
                   $ionicScrollDelegate, $ionicSlideBoxDelegate, $location, $log, $ocLazyLoad, $pwaRequest, $q,
                   $rootScope, $session, $state, $templateCache, $timeout, $translate, $window, AdmobService,
                   Application, Customer, Dialog, Facebook, FacebookConnect, Padlock,
                   Pages, SB) {

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

        $rootScope.$on("$stateNotFound", function(event, unfoundState, fromState, fromParams) {
            console.log("$stateNotFound", unfoundState.to); // "lazy.state"
            console.log("$stateNotFound", unfoundState.toParams); // {a:1, b:2}
            console.log("$stateNotFound", unfoundState.options); // {inherit:false} + default options
        });

        // Listeners for network events!
        $window.addEventListener("online", function () {
            $rootScope.isOnline = true;
            $rootScope.isOffline = false;
        });

        $window.addEventListener("offline", function () {
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

        $rootScope.isNotAvailableOffline = function () {
            if ($rootScope.isOffline) {
                Dialog.alert("Offline mode",
                    "This feature is not available when offline!", "Dismiss", 2350);
                return true;
            }
            return false;
        };

        $rootScope.isNotAvailableInOverview = function () {
            if (isOverview) {
                Dialog.alert("Overview",
                    "This feature is not available in this preview", "Dismiss", -1);
                return true;
            }
            return false;
        };

        $ionicPlatform.ready(function () {
            $ionicNavBarDelegate.align("center");
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

                    $pwaRequest.post("front/pwa/init", {
                        data: {
                            add_language: true,
                            device_uid: $session.getDeviceUid(),
                            device_width: deviceScreen.width,
                            device_height: deviceScreen.height,
                            isPwa: isPwa,
                            version: "4.17.3"
                        },
                        timeout: 20000,
                        cache: !isOverview,
                        refresh: refresh,
                        network_promise: networkPromise
                    }).then(function (data) {
                        var load = data.loadBlock;

                        // Translations & locale!
                        $translate.translations = data.translationBlock;

                        if (!$session.getId()) {
                            $session.setId(data.loadBlock.customer.token);
                        }

                        // Populate main objects!
                        Application.populate(data.loadBlock);
                        $ionicConfig.backButton.icon(Application.getBackIcon());
                        Customer.populate(data.loadBlock.customer);
                        Customer.setFacebookLogin(data.loadBlock.application.facebook);
                        Pages.populate(data.featureBlock);

                        // Login Facebook HTML5!
                        if (LOGIN_FB) {
                            Customer.loginWithFacebook(fbtoken);
                        }

                        var HomepageLayout = $injector.get("HomepageLayout");

                        // App keyboard & StatusBar!
                        if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
                            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(false);
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

                        if (Customer.isLoggedIn()) {
                            $rootScope.$broadcast(SB.EVENTS.AUTH.loginSuccess);
                        } else {
                            $rootScope.$broadcast(SB.EVENTS.AUTH.logoutSuccess);
                        }

                        $ionicNavBarDelegate.align("center");
                        $timeout(function () {
                            $ionicNavBarDelegate.showBar(false);
                        });

                        $rootScope.$on("$ionicView.loaded", function (event, loadViewData) {
                            if (loadViewData.stateName !== "home") {
                                $timeout(function () {
                                    $ionicNavBarDelegate.showBar(true);
                                }, 5);
                            } else {
                                $timeout(function () {
                                    $ionicNavBarDelegate.showBar(!!HomepageLayout.properties.options.autoSelectFirst);
                                }, 5);
                            }
                        });

                        // Handler for overview & navbar, Only for overview!
                        $rootScope.$on('$stateChangeSuccess', function (event, toState, toStateParams, fromState, fromStateParams) {
                            if (parent && (typeof parent.postMessage === 'function') && (parent !== window)) {
                                parent.postMessage('state.go', DOMAIN);
                            }
                        });

                        $rootScope.$on('$stateChangeStart', function (event, toState, toStateParams, fromState, fromStateParams) {
                            $rootScope.app_is_locked = Application.is_locked &&
                                !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode);

                            // BO Lock takes over all!
                            if ($rootScope.app_is_bo_locked) {
                                $ionicHistory.nextViewOptions({
                                    disableBack: true
                                });

                                $state.go('locked');
                            } else if ($rootScope.app_is_locked && (toState.name !== 'padlock-view')) {
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

                            if ($rootScope.app_is_bo_locked) {
                                $ionicHistory.nextViewOptions({
                                    disableBack: true
                                });

                                $state.go('locked');
                            } else if (!$rootScope.app_is_locked && Application.is_locked) {
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

                        // Reducing overview footprint!
                        $rootScope.isOverview = isOverview;
                        if ($rootScope.isOverview) {
                            if (parent && (typeof parent.postMessage === 'function') && (parent !== window)) {
                                parent.postMessage('overview.loaded', DOMAIN);
                            }
                        }

                        Application.loaded = true;

                        // Should be better here!
                        if (data.cssBlock && data.cssBlock.css) {
                            var css = document.createElement('style');
                            css.type = 'text/css';
                            css.innerHTML = data.cssBlock.css;
                            document.body.appendChild(css);
                        }

                        networkPromise.promise
                            .then(function (networkPromiseResult) {
                                // On refresh cache success, refresh pages, then refresh homepage!
                                Pages.populate(networkPromiseResult.featureBlock);
                                $rootScope.$broadcast(SB.EVENTS.CACHE.layoutReload);
                            });

                        // Check for padlock!
                        var currentState = $ionicHistory.currentStateName();

                        // Backoffice locks takes precedence over padlock
                        if (load.application.is_bo_locked) {
                            $rootScope.app_is_bo_locked = true;

                            $ionicHistory.nextViewOptions({
                                disableBack: true
                            });

                            $state.go("locked");
                        } else if ($rootScope.app_is_locked && (currentState !== "padlock-view")) {
                            $state.go("padlock-view");
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

                        window.Features.featuresToLoadOnStart.forEach(function (bundle) {
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
                            }).catch(function (error) {});
                        });

                        // Calling all deferred elements!
                        Application.deferDeps();

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

