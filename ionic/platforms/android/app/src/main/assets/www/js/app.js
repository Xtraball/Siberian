/**
 * Application Bootstrap
 *
 * @version 4.20.17
 */

window.momentjs_loaded = false;
window.extractI18n = true;
var DEBUG = false;

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

// App language!
// First check in localStorage
var CURRENT_LANGUAGE = 'en';
var setupLanguage = function () {
    var storageLanguage = localStorage.getItem('pwa-cache-' + APP_KEY + '/registry-index/sb-current-language');
    if (storageLanguage !== null) {
        storageLanguage = storageLanguage.replace(/"/g, '');
        if (AVAILABLE_LANGUAGES.indexOf(storageLanguage) >= 0) {
            CURRENT_LANGUAGE = storageLanguage;
        }
    } else if (navigator.language) {
        var tmpLanguage = navigator.language.replace('-', '_');
        try {
            if (AVAILABLE_LANGUAGES.indexOf(tmpLanguage) >= 0) {
                CURRENT_LANGUAGE = tmpLanguage;
            } else if (AVAILABLE_LANGUAGES.indexOf(tmpLanguage.split('_')[0]) >= 0) {
                CURRENT_LANGUAGE = tmpLanguage.split('_')[0];
            }
        } catch (e) {
            console.error('[Language] fallback to "en" due to navigator.language error.');
            CURRENT_LANGUAGE = 'en';
        }
        // We save it the first time we retrieve through 'navigator.language'
        localStorage.setItem('pwa-cache-' + APP_KEY + '/registry-index/sb-current-language',
            '"' + CURRENT_LANGUAGE + '"');
    }
};
setupLanguage();

angular.module('lodash', []).factory('_', ['$window', function ($window) {
    return $window._;
}]);

var semver = {compare: function (a, b, specificity) {var pa = a.split('.');var pb = b.split('.');var sentinels = {'major': 1, 'minor': 2, 'patch': 3};for (var i = 0; i < (sentinels[specificity] || 3); i++) {na = Number(pa[i]);nb = Number(pb[i]);if (na > nb || !isNaN(na) && isNaN(nb)) {return 1;}if (na < nb || isNaN(na) && !isNaN(nb)) {return -1;}}return 0;}, isGreater: function (a, b, specificity) {return this.compare(a, b, specificity) === 1;}, isLess: function (a, b, specificity) {return this.compare(a, b, specificity) === -1;}, isEqual: function (a, b, specificity) {return this.compare(a, b, specificity) === 0;}};

var App = angular.module('starter', ['ionic', 'lodash', 'ngRoute', 'ngCordova', 'ngSanitize', 'ngQueue',
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
                            config.headers['XSB-AUTH'] = sessionId;
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
        $compileProvider.imgSrcSanitizationWhitelist(/^\s*(https?|ftp|file|blob|ionic):|data:image/);
        $httpProvider.defaults.withCredentials = true;
        $ionicConfigProvider.views.swipeBackEnabled(false);
        $ionicConfigProvider.backButton.text('');
        $ionicConfigProvider.backButton.previousTitleText(false);
    })
    .run(function ($injector, $ionicConfig, $ionicHistory, $ionicNavBarDelegate, $ionicPlatform, $ionicPopup,
                   $ionicScrollDelegate, $ionicSlideBoxDelegate, $location, $log, $ocLazyLoad, $pwaRequest, $q,
                   $rootScope, $session, $state, $templateCache, $timeout, $translate, $window, AdmobService,
                   Analytics, Application, Customer, Codescan, Dialog, Padlock,
                   Pages, SB, InAppLinks) {

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

        // Display previewer notice!
        if (IS_PREVIEW) {
            $rootScope.previewerNotice = true;
        }

        // Listeners for network events!
        $window.addEventListener('online', function () {
            $rootScope.isOnline = true;
            $rootScope.isOffline = false;
        });

        $window.addEventListener('offline', function () {
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

            var loadApp = function (refresh) {
                // Fallback empty objects for browser!
                $window.cordova = $window.cordova || {};
                $window.device = $window.device || {};

                try {
                    // Set device footprint
                    $window.footprint = $window.device.manufacturer + ' - ' +
                        $window.device.model + ' - ' +
                        $window.device.platform + ' ' +
                        $window.device.version;
                } catch (e) {
                    $window.footprint = 'Unknown device';
                }

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
                            footprint: $window.footprint,
                            user_language: CURRENT_LANGUAGE,
                            version: '4.20.22'
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
                        $ionicConfig.backButton.icon('none');

                        Customer.populate(data.loadBlock.customer);
                        Pages.populate(data.featureBlock);

                        var HomepageLayout = $injector.get('HomepageLayout');

                        // Append custom CSS/SCSS to the page!
                        if (data.cssBlock && data.cssBlock.css) {
                            var css = document.createElement('style');
                            css.setAttribute('type', 'text/css');
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

                        if (!IS_PREVIEW) {
                            $ionicPlatform.on('resume', function (resumeResult) {
                                Analytics.storeOpening().then(function (result) {
                                    Analytics.data.storeClosingId = result.id;
                                });

                                $rootScope.onPause = false;
                            });

                            $rootScope.onPause = false;
                            $ionicPlatform.on('pause', function (pauseResult) {
                                $rootScope.onPause = true;
                                Analytics.storeClosing();

                                // Ensure we won't update an app while the previewer is in progress!
                                $ocLazyLoad
                                .load("./features/previewer/previewer.bundle.min.js")
                                .then(function () {
                                    try {
                                        $injector.get("Previewer").fileExists(function () {
                                                console.info("[PREVIEWER] Preview in progress, aborting.");
                                            },
                                            function () {
                                                //console.info("[PREVIEWER] No previewer loaded, continue.");
                                            });
                                    } catch (e) {
                                        console.log("[PREVIEWER - WARNING] " + e.message);
                                    }
                                })
                                .catch(function (error) {});
                            });
                        }

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
                            if (!$window.localStorage.getItem('first_running')) {
                                $window.localStorage.setItem('first_running', 'true');
                                Analytics.storeInstallation();
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

                        try {
                            AdmobService.init(load.application.admob);
                        } catch (error) {
                            if (DEBUG) {
                                $log.error('Unable to initialize AdMob.', error);
                            }
                        }

                        // Only for iOS 14* we can ask the ATT modal (if Admob didn't first)
                        try {
                            if (DEVICE_TYPE === SB.DEVICE.TYPE_IOS &&
                                load.application.requestTrackingAuthorization === true) {
                                cordova.plugins.CorePlugin.requestTrackingAuthorization();
                            }
                        } catch (error) {
                            if (DEBUG) {
                                $log.error('Unable to request ATT.', error);
                            }
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
                            }
                        });

                        // Listen for any inAppLink events
                        InAppLinks.listen();

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
                                            if (['code_scan', 'radio', 'padlock', 'tabbar_account'].indexOf(feat.code) >= 0) {
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
                        $ocLazyLoad
                        .load('./dist/lazy/moment.min.js')
                        .then(function () {
                            window.momentjs_loaded = true;
                            try {
                                var tmpLang = CURRENT_LANGUAGE.replace('_', '-').toLowerCase();
                                var langPriority = [tmpLang, tmpLang.split('-')[0], 'en'];
                                moment.locale(langPriority);
                            } catch (e) {
                                moment.locale('en');
                            }
                        });

                        $ocLazyLoad.load('./dist/lazy/angular-carousel.min.js');
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
                                        console.log(e, e.message);
                                    }
                                }
                            }).catch(function (error) {
                            });
                        });

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
                                try {
                                    $ocLazyLoad
                                    .load('./features/previewer/previewer.bundle.min.js')
                                    .then(function () {
                                        $injector.get('Previewer').deleteFile();
                                    });
                                } catch (e) {
                                    //
                                }
                                $window.webview.close();
                            });
                        } else {
                            // Saving shared device path, path is unique on every device*
                            try {
                                var sharedCdvModulePath = null;
                                if (DEVICE_TYPE === 1) {
                                    sharedCdvModulePath = Ionic.WebView.convertFileSrc(cordova.file.dataDirectory + 'module.js');
                                } else {
                                    sharedCdvModulePath = cordova.file.tempDirectory + 'module.js';
                                }
                                localStorage.setItem('shared-cdv-module-path', sharedCdvModulePath);
                            } catch (e) {
                                // Ignore me!
                            }
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
