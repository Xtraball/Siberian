angular.module('starter').provider('HomepageLayout', function () {
    var self = this;

    self.layout_ids = {};

    self.getLayoutIdForValueId = function (valueId) {
        var layoutId = 1;
        try {
            for (var i in self.pages) {
                if (self.pages[i].value_id == valueId) {
                    layoutId = self.pages[i].layout_id;
                }
            }
        } catch (e) {
            layoutId = 1;
        }

        return layoutId;
    };

    self.setLayoutIdForValueId = function (value_id, layout_id) {
        self.layout_ids[value_id] = layout_id;
        // Live overview layout change!
        for (var i in self.pages) {
            if (self.pages[i].value_id == value_id) {
                self.pages[i].layout_id = layout_id;
            }
        }
    };

    self.$get = function ($injector, $ionicSlideBoxDelegate, $ionicPlatform, $ionicHistory, $ionicSideMenuDelegate,
                          $location, $log, $q, $rootScope, $stateParams, $timeout, $window, LinkService, Analytics,
                          Customer, Pages, Padlock, Modal) {
        var HomepageLayout = {};

        // Hooks!
        HomepageLayout.load_hooks = [];
        HomepageLayout.more_modal = null;

        /**
         *
         * @param feature
         * @param scope
         * @returns {boolean}
         */
        HomepageLayout.openFeature = function (feature, scope) {
            if (scope === undefined) {
                scope = $rootScope;
            }

            // Close more when changing feature.
            if (HomepageLayout.more_modal) {
                HomepageLayout.more_modal.hide();
                scope.tabbar_is_visible = true;
                scope.pages_list_is_visible = false;
            }

            // Close any open modal first!
            if (Modal.is_open) {
                Modal.current_modal.hide();
            }

            // Clear history for side-menu feature!
            switch (Pages.data.layout.position) {
                case 'left':
                case 'right':
                    if ($ionicSideMenuDelegate.isOpenLeft()) {
                        $ionicSideMenuDelegate.toggleLeft();
                    }
                    if ($ionicSideMenuDelegate.isOpenRight()) {
                        $ionicSideMenuDelegate.toggleRight();
                    }

                    if (feature.code !== 'padlock') { // do not clear history if we open the padlock!
                        if (feature.path !== $location.path()) {
                            $ionicHistory.nextViewOptions({
                                historyRoot: true,
                                disableAnimate: false
                            });
                        }
                    }
                    break;
            }

            switch (true) {
                case (feature.code === 'tabbar_account'):
                    Analytics.storePageOpening({
                        id: 0
                    });

                    Customer.loginModal(scope);

                    break;

                case (feature.code === 'tabbar_more'):
                    HomepageLayout.getFeatures().then(function (features) {
                        scope.tabbar_is_visible = false;
                        scope.pages_list_is_visible = true;
                        scope.features = features;

                        scope.closeMore = function () {
                            HomepageLayout.more_modal.hide();
                            scope.tabbar_is_visible = true;
                            scope.pages_list_is_visible = false;
                        };

                        // That's weird!
                        scope.goTo = function (goToFeature) {
                            HomepageLayout.openFeature(goToFeature, scope);
                        };

                        Modal
                            .fromTemplateUrl(HomepageLayout.getModalTemplate(), {
                                scope: scope
                            })
                            .then(function (modal) {
                                HomepageLayout.more_modal = modal;
                                HomepageLayout.more_modal.show();

                                // pages_list_is_visible is true means that the ...
                                // button in the main menu was clicked!
                                $ionicPlatform.onHardwareBackButton(function (e) {
                                    if (scope.pages_list_is_visible) {
                                        scope.closeMore();
                                    }
                                });
                            });
                    });

                    break;

                case ($rootScope.isOffline && !feature.offline_mode && $rootScope.isNotAvailableOffline()):
                    break;

                case (feature.is_link):
                    LinkService.openLink(feature.url, {
                        'hide_navbar': !!feature.hide_navbar,
                        'use_external_app': !!feature.use_external_app
                    });
                    Analytics.storePageOpening(feature);

                    break;

                default:

                    Analytics.storePageOpening(feature);

                    if (!$injector.get('Application').is_customizing_colors &&
                        HomepageLayout.properties.options.autoSelectFirst) {
                        if (feature.path !== $location.path()) {
                            $ionicHistory.nextViewOptions({
                                historyRoot: true,
                                disableAnimate: false
                            });
                            $location.path(feature.path).replace();
                        }
                    } else {
                        $location.path(feature.path);
                    }
            }
        };

        /** Register hooks to be called when homepage is done. */
        HomepageLayout.registerHook = function (hook) {
            if (typeof hook === 'function') {
                HomepageLayout.load_hooks.push(hook);
            }
        };

        /** Call all registered hooks. */
        HomepageLayout.callHooks = function () {
            /** Call registered hooks */
            for (var i = 0; i < HomepageLayout.load_hooks.length; i++) {
                HomepageLayout.load_hooks[i]();
            }

            /** Slidebox update for resize/orientation */
            $timeout(function () {
                $ionicSlideBoxDelegate.update();
            }, 800);
        };

        HomepageLayout.getTemplate = function () {
            var layout_code = HomepageLayout.properties.layoutCode;
            var layout_id = HomepageLayout.properties.layoutId;
            if ($injector.has(layout_code)) {
                return $injector.get(layout_code).getTemplate();
            }
            return 'templates/home/' + layout_id + '/view.html';
        };

        HomepageLayout.getModalTemplate = function () {
            var layout_code = HomepageLayout.properties.layoutCode;
            if ($injector.has(layout_code)) {
                return $injector.get(layout_code).getModalTemplate();
            }
            return 'templates/home/modal/view.html';
        };

        HomepageLayout._initData = function () {
            HomepageLayout.data = null;
            HomepageLayout.need_to_build_the_options = true;
            HomepageLayout.options = null;
            HomepageLayout.is_initialized = false;
            HomepageLayout.dataLoading = false;

            HomepageLayout.properties = {
                options: {
                    // if no option is selected, the user will be automatically redirected to the first one
                    autoSelectFirst: false,
                    // is it the root page of current option
                    isRootPage: false,
                    current: null
                },
                menu: {
                    /**
                     * === position ===
                     * normal: menu displayed in home page (used in combination with visibility 'home')
                     * left: menu displayed in left side
                     * right: menu displayed in right side
                     * bottom: menu displayed in bottom side
                     */
                    position: 'normal',
                    /**
                     * === visibility ===
                     * homepage: menu displayed in home page (used in combination with position 'normal')
                     * toggle: menu visibility is togglable with a button
                     * always: menu is always visible
                     */
                    visibility: 'homepage',
                    // status of menu visibility (used in 'toggle' mode only)
                    isVisible: false,
                    // sidebars width
                    sidebarLeftWidth: '120',
                    sidebarRightWidth: '120'
                }
            };
        };

        HomepageLayout.getData = function () {
            var deferred = $q.defer();

            // filter features
            if (HomepageLayout.data === null) {
                if (HomepageLayout.dataLoading) {
                    // already loading
                    $timeout(function () {
                        deferred.resolve(HomepageLayout.getData());
                    });
                } else {
                    Pages.ready
                        .then(function () {
                            HomepageLayout.dataLoading = true;

                            HomepageLayout.data = Pages.data;

                            self.pages = Pages.data.pages;
                            self.layout_ids = Pages.data.layouts;

                            HomepageLayout.properties.layoutId = Pages.data.layout_id;
                            HomepageLayout.properties.layoutCode = Pages.data.layout_code;
                            HomepageLayout.properties.layoutOptions = Pages.data.layout.layout_options;
                            HomepageLayout.properties.tabbar_is_transparent = Pages.data.tabbar_is_transparent;

                            // Check for a custom width
                            if ((typeof HomepageLayout.properties.layoutOptions !== 'undefined') &&
                                (typeof HomepageLayout.properties.layoutOptions.sidebarWidth !== 'undefined')) {
                                switch (HomepageLayout.properties.layoutOptions.sidebarWidthUnit) {
                                    case 'pixel':
                                        HomepageLayout.properties.menu.sidebarLeftWidth =
                                            HomepageLayout.properties.layoutOptions.sidebarWidthPixel;
                                        break;
                                    case 'percentage':
                                        var width = $window.innerWidth;
                                        HomepageLayout.properties.menu.sidebarLeftWidth =
                                            (width / 100 * HomepageLayout.properties.layoutOptions.sidebarWidth);
                                        break;
                                }
                            }

                            HomepageLayout._init();
                            HomepageLayout.dataLoading = false;

                            deferred.resolve(HomepageLayout.data);
                        });
                }
            } else {
                if (HomepageLayout.need_to_build_the_options) {
                    HomepageLayout._buildOptions();
                }

                deferred.resolve(HomepageLayout.data);
            }

            return deferred.promise;
        };

        HomepageLayout.getOptions = function () {
            var deferred = $q.defer();

            // filter features
            if (HomepageLayout.options === null) {
                HomepageLayout.getData()
                    .then(function (data) {
                        deferred.resolve(HomepageLayout.options);
                    }, function (err) {
                        deferred.reject(err);
                    });
            } else {
                deferred.resolve(HomepageLayout.options);
            }

            return deferred.promise;
        };

        HomepageLayout.getActiveOptions = function () {
            var deferred = $q.defer();

            HomepageLayout.getOptions()
                .then(function (options) {
                    // filter active options
                    options = options.reduce(function (options, option) {
                        if (option.is_active) {
                            options.push(option);
                        }
                        return options;
                    }, []);

                    HomepageLayout.data.customer_account.url = Customer.isLoggedIn() ?
                        HomepageLayout.data.customer_account.edit_url :
                        HomepageLayout.data.customer_account.login_url;
                    HomepageLayout.data.customer_account.path = Customer.isLoggedIn() ?
                        HomepageLayout.data.customer_account.edit_path :
                        HomepageLayout.data.customer_account.login_path;

                    if (HomepageLayout.data.customer_account.is_visible) {
                        options.push(HomepageLayout.data.customer_account);
                    }

                    deferred.resolve(options);
                });
            return deferred.promise;
        };

        HomepageLayout.getFeatures = function () {
            var deferred = $q.defer();

            HomepageLayout.getActiveOptions().then(function (options) {
                /*
                 * features
                 * - features.layoutId: layout id
                 * - features.options: filtered options, including customer_account if visible
                 * - features.overview: filtered options truncated if > to data.limit_to,
                 *      and concatenated with more_items
                 */

                var features = {
                    layoutId: HomepageLayout.data.layout_id,
                    layoutCode: HomepageLayout.data.layout_code,
                    layoutOptions: HomepageLayout.data.layout.layout_options,
                    options: options,
                    overview: {
                        hasMore: false,
                        options: [],
                        paged_options: [],
                        limit: HomepageLayout.data.limit_to
                    },
                    data: HomepageLayout.data
                };

                var limit = features.overview.limit;

                if (limit !== null && limit > 0 && features.options.length > limit) {
                    if (HomepageLayout.data.layout.use_horizontal_scroll) {
                        var paged_options = [];
                        for (var i = 0; i < features.options.length; i++) {
                            paged_options.push(features.options[i]);
                            if (((i + 1) % limit) == 0) {
                                features.overview.paged_options.push(paged_options);
                                paged_options = [];
                            }
                        }
                        if (paged_options.length) {
                            features.overview.paged_options.push(paged_options);
                        }
                    } else {
                        // truncate to (limit - 1)
                        for (var i = 0; i < (limit - 1); i++) {
                            features.overview.options.push(features.options[i]);
                        }
                        features.overview.hasMore = true;
                    }
                } else if (HomepageLayout.data.layout.use_horizontal_scroll) {
                    features.overview.paged_options = [features.options];
                } else {
                    features.overview.options = features.options;
                }

                // MORE ...!
                var more_button = {
                    name: features.data.more_items.name,
                    icon_url: features.data.more_items.icon_url,
                    icon_is_colorable: features.data.more_items.icon_is_colorable,
                    code: features.data.more_items.code,
                    url: 'tabbar_more'
                };

                // Inject custom layout feature hooks!
                var layout_code = HomepageLayout.data.layout_code;
                if ($injector.has(layout_code)) {
                    features = $injector.get(layout_code).features(features, more_button);

                    // Hook orientationchange/viewenter home!
                    HomepageLayout.registerHook(function () {
                        $injector.get(layout_code).onResize();
                    });

                    window.addEventListener('orientationchange', function () {
                        $injector.get(layout_code).onResize();
                    });
                } else if (features.overview.hasMore) {
                    features.overview.options.push(more_button);
                }

                // Slidebox update for resize/orientation!
                $timeout(function () {
                    $ionicSlideBoxDelegate.update();
                }, 200);


                features.first_option = false;
                if (HomepageLayout.properties.options.autoSelectFirst && (features.options.length !== 0)) {
                    features.first_option = features.options[0];
                }

                deferred.resolve(features);
            });

            return deferred.promise;
        };

        HomepageLayout._updateFromUrl = function (url) {
            if (HomepageLayout.options === null) {
                return;
            }

            if ($stateParams.value_id) {
                var optionId = parseInt($stateParams.value_id);

                // get current option from URL
                var currentOption = HomepageLayout.options.reduce(function (currentOption, option) {
                    if (option.id === optionId) {
                        if (option.url === url) {
                            HomepageLayout.properties.options.isRootPage = true;
                            HomepageLayout.properties.menu.isVisible =
                                (HomepageLayout.properties.menu.visibility === 'always');
                        } else {
                            HomepageLayout.properties.options.isRootPage = false;
                            HomepageLayout.properties.menu.isVisible =
                                ((HomepageLayout.properties.menu.visibility === 'always') || false);
                        }

                        currentOption = option;
                    }
                    return currentOption;
                }, null);

                if (currentOption === null) {
                    HomepageLayout.properties.options.isRootPage = false;
                } else if (HomepageLayout.properties.options.current !== currentOption) {
                    HomepageLayout.properties.options.current = currentOption;
                }

                return currentOption;
            } else {
                if (/customer\/mobile_account/.test(url) || /cms\/mobile_privacypolicy/.test(url)) {
                    HomepageLayout.properties.options.isRootPage = /customer\/mobile_account_login/.test(url);
                    HomepageLayout.properties.menu.isVisible = HomepageLayout.properties.menu.visibility === 'always';
                    HomepageLayout.properties.options.current = null;
                } else {
                    return null;
                }
            }
        };

        HomepageLayout.fireLocationChanged = function () {
            HomepageLayout._updateFromUrl($location.absUrl());
        };

        HomepageLayout._init = function () {
            var options = HomepageLayout._buildOptions();

            HomepageLayout.properties.menu.position = HomepageLayout.data.layout.position;
            HomepageLayout.properties.menu.visibility = HomepageLayout.data.layout.visibility;
            HomepageLayout.properties.options.autoSelectFirst =
                (HomepageLayout.properties.menu.visibility !== 'homepage');

            HomepageLayout._updateFromUrl($location.absUrl());

            HomepageLayout.is_initialized = true;
        };

        HomepageLayout.setLayoutId = function (value_id, layout_id) {
            self.setLayoutIdForValueId(value_id, layout_id);
        };

        HomepageLayout._buildOptions = function () {
            HomepageLayout.options = HomepageLayout.data.pages.reduce(function (options, option) {
                if (!option.is_locked || Customer.can_access_locked_features || Padlock.unlocked_by_qrcode) {
                    if ((!Customer.isLoggedIn() && !Padlock.unlocked_by_qrcode) || (option.code !== 'padlock')) {
                        // use is logged or not padlock feature
                        options.push(option);
                    }
                }
                return options;
            }, []);

            HomepageLayout.need_to_build_the_options = false;
            return HomepageLayout.options;
        };

        HomepageLayout._initData();

        return {
            leftAreaSize: 150,
            openFeature: function (feature, scope) {
                return HomepageLayout.openFeature(feature, scope);
            },
            registerHook: function (hook) {
                return HomepageLayout.registerHook(hook);
            },
            callHooks: function () {
                return HomepageLayout.callHooks();
            },
            getTemplate: function () {
                return HomepageLayout.getTemplate();
            },
            getModalTemplate: function () {
                return HomepageLayout.getModalTemplate();
            },
            getData: function () {
                return HomepageLayout.getData();
            },
            unsetData: function () {
                HomepageLayout._initData();
                $rootScope.$broadcast('tabbarStatesChanged');
            },
            getOptions: function () {
                return HomepageLayout.getOptions();
            },
            getActiveOptions: function () {
                return HomepageLayout.getActiveOptions();
            },
            getFeatures: function () {
                return HomepageLayout.getFeatures();
            },
            isInitialized: function () {
                return HomepageLayout.is_initialized;
            },
            unlockByQRCode: function (qrcode) {
                return HomepageLayout.unlockByQRCode(qrcode);
            },
            setNeedToBuildTheOptions: function (need_to_build_the_options) {
                HomepageLayout.options = null;
                HomepageLayout.need_to_build_the_options = need_to_build_the_options;
            },
            rebuildOptions: function () {
                HomepageLayout._buildOptions();
            },
            setLayoutId: function (value_id, layout_id) {
                HomepageLayout.setLayoutId(value_id, layout_id);
            },
            properties: HomepageLayout.properties,
            app: {
                // trigger event from app root
                fireLocationChanged: HomepageLayout.fireLocationChanged
            }
        };
    };
});
;angular.module('starter').provider('$pwaCache', function () {
    var provider = this;

    provider.$get = function () {
        var provider = {};

        if (typeof window.localforage === 'undefined') {
            provider = {
                isEnabled: false
            };

            return provider;
        }

        provider = {
            isEnabled: true,
            defaultDrivers: [window.localforage.INDEXEDDB, window.localforage.LOCALSTORAGE],
            defaultStoreName: 'content-cache',
            valueidStoreName: 'valueid-index',
            registryStoreName: 'registry-index',
            cacheKey: 'pwa-cache-' + APP_KEY,
            /** Fixed to 64MB */
            cacheMaxSize: 64000000,
            /** Caches */
            defaultCache: null,
            valueidCache: null,
            registryCache: null,
            backgroundImages: []
        };

        provider.defaultCache = window.localforage.createInstance({
            driver: provider.defaultDrivers,
            name: provider.cacheKey,
            storeName: provider.defaultStoreName,
            size: provider.cacheMaxSize
        });

        provider.valueidCache = window.localforage.createInstance({
            driver: provider.defaultDrivers,
            name: provider.cacheKey,
            storeName: provider.valueidStoreName,
            size: (provider.cacheMaxSize / 16)
        });

        provider.registryCache = window.localforage.createInstance({
            driver: window.localforage.LOCALSTORAGE,
            name: provider.cacheKey,
            storeName: provider.registryStoreName,
            size: (provider.cacheMaxSize / 16)
        });

        /**
         * Default cache for all http based requests & assets
         *
         * @returns {null}
         */
        provider.getDefaultCache = function () {
            return provider.defaultCache;
        };

        /**
         *
         * @returns {*}
         */
        provider.getValueidCache = function () {
            return provider.valueidCache;
        };

        /**
         *
         * @returns {*}
         */
        provider.getRegistryCache = function () {
            return provider.registryCache;
        };

        return provider;
    };
});
;/*global
    angular, window, window.localforage, _, isNativeApp, console
 */

/**
 * $pwaRequest
 *
 * Progressive Web App request handler, offline-first purpose
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("FallbackPromise", function($q) {
    return {
        resolved: false,
        decorate: function(promise) {
            promise.success = function(callback) {
                promise.then(callback);

                return promise;
            };

            promise.error = function(callback) {
                promise.then(null, callback);

                return promise;
            };
        },
        defer: function() {
            var deferred = $q.defer();

            this.decorate(deferred.promise);

            return deferred;
        }
    };
});

angular.module("starter").provider("$pwaRequest", function httpCacheLayerProvider() {

    var provider = {
        debug           : true,
        queue           : null,
        defaultCache    : null,
        valueidCache    : null,
        registryCache   : null,
        cacheIsEnabled  : false
    };

    provider.$get = [
        "$pwaCache", "$injector", "$rootScope", "$session", "$translate", "$http", "$log", "$q", "$queue", "FallbackPromise", "Url",
        function httpCacheLayerFactory($pwaCache, $injector, $rootScope, $session, $translate, $http, $log, $q, $queue, FallbackPromise, Url) {

            /** Shortcut cache */
            provider.cacheIsEnabled     = $pwaCache.isEnabled;
            provider.defaultCache       = $pwaCache.getDefaultCache();
            provider.valueidCache       = $pwaCache.getValueidCache();
            provider.registryCache      = $pwaCache.getRegistryCache();

            var httpCacheLayerConfig = {
                debug           : provider.debug
            };

            provider.handleRequest = function(options) {

                /** Disable $http cache */
                options = angular.extend({}, options, {
                    cache: false
                });

                $http(options).then(function(response) {

                    if(options.first_cache) {
                        $log.debug("caching: response success", response, cachedResponse);
                    } else {
                        $log.debug("caching again: response success", response, cachedResponse);
                    }

                    var cachedResponse = {
                        expires_at  : options.expires_at,
                        touched_at  : options.touched_at,
                        data        : angular.copy(response.data)
                    };

                    provider.defaultCache.setItem(options.cacheKey, cachedResponse);

                    /** When done & cached, send the data */
                    options.network_promise.resolve(angular.copy(response.data));

                    if(options.return_response || options.pullToRefresh) {
                        options.deferred_promise.resolve(angular.copy(response.data));
                    }

                }, function(response) {

                    $log.debug("response error", response);

                    var data = {
                        error       : true,
                        message     : $translate.instant("The request returned a 400 HTTP Code, with no message.")
                    };

                    if(_.isObject(response) && _.isObject(response.data)) {
                        data = response.data;
                    }

                    if(_.isObject(response) && (response.status === 410)) {
                        $log.debug("remove gone resource");
                        provider.defaultCache.removeItem(options.cacheKey);
                    }

                    /** On error do not cache, but send the data immediately */
                    options.network_promise.reject(data);

                    if(options.return_response) {
                        options.deferred_promise.reject(data);
                    }

                    if(options.pullToRefresh) {
                        data.message = $translate.instant("Unable to refresh content, please try again later.");

                        options.deferred_promise.reject(data);
                    }

                }).catch(function(errrrrr) {
                    $log.error("Catched error: " + errrrrr);
                });

            };

            if(provider.queue === null) {
                provider.queue = $queue.queue(provider.handleRequest, {
                    delay           : 100,
                    paused          : false,
                    persistent      : true,
                    max_concurrent  : 5
                });
                provider.queue.start();
            }

            function httpCacheLayer(httpCacheLayerConfig) {

                var httpWrapper = function(requestOptions) {

                    var current = Math.trunc((new Date()).getTime()/1000);

                    /** Normalizing options */
                    var options = _.extend(requestOptions, {
                        method              : _.upperCase(_.trim(_.get(requestOptions, "method", "GET"))),
                        cache               : !!_.get(requestOptions, "cache", true),
                        url                 : _.trim(_.get(requestOptions, "url")),
                        pullToRefresh       : !!_.get(requestOptions, "refresh", false),
                        imageProxy          : !!_.get(requestOptions, "imageProxy", false),
                        requestData         : _.get(requestOptions, "data", {}),
                        expires_at          : _.get(requestOptions, "expires_at", (current + 3600)),
                        touched_at          : _.get(requestOptions, "touched_at", (current)),
                        network_promise     : _.get(requestOptions, "network_promise", $q.defer()),
                        timeout             : _.get(requestOptions, "timeout", 30000),
                        deferred_promise    : FallbackPromise.defer()
                    });

                    options.deferred_promise.promise.__name__ = "pwa-" + Math.random();

                    /** Return direct request when cache is not active */
                    if(!provider.cacheIsEnabled || !options.cache) {

                        if($rootScope.isOnline) {
                            $log.debug("direct request, no cache.", options.url);

                            $http(requestOptions)
                                .then(function(response) {

                                    options.deferred_promise.resolve(response.data);

                                }, function(response) {

                                    if(response && response.data) {

                                        if(response.data.message === undefined) {
                                            response.data.message = $translate.instant("An unknown error occurred.");
                                        }

                                        options.deferred_promise.reject(response.data);

                                    } else {
                                        var error = {
                                            error       : true,
                                            message     : $translate.instant("The resource is not reachable, check your network connection.")
                                        };

                                        options.deferred_promise.reject(error);
                                    }

                                }).catch(function(err) {

                                var error = {
                                    error       : true,
                                    message     : $translate.instant("[Network] The resource is not reachable, check your network connection.")
                                };

                                options.deferred_promise.reject(error);
                            });

                        } else {
                            var error = {
                                error       : true,
                                message     : $translate.instant("[Network] Device is offline, aborting request.")
                            };

                            options.deferred_promise.reject(error);
                        }

                        console.log("deferred", options.deferred_promise);

                        return options.deferred_promise.promise;
                    }

                    /** Default cache key */
                    var cacheKey = options.url;

                    /** Case when it's an proxied image, the key is not the proxy url, but the original resource uri. */
                    if(options.imageProxy) {
                        cacheKey = options.requestData.resource;

                        if(cacheKey === undefined) {
                            return $q.reject({
                                error: true,
                                message: $translate.instant("Resource is `undefined`.")
                            });
                        }
                    }

                    /** Index of value_ids, this way we can trigger targeted cached refresh based on server changes. */
                    var value_id = _.get(options.urlParams, "value_id", false);
                    var valueidCachekey = "valueid_" + value_id;
                    if(value_id !== false) {
                        provider.valueidCache.getItem(valueidCachekey)
                            .then(function(cached_array) {

                                var copy = angular.copy(cached_array);

                                if(copy === null) {

                                    provider.valueidCache.setItem(valueidCachekey, [cacheKey]);

                                } else {

                                    /**
                                     * Clear prior to save if refresh
                                     */
                                    if(options.pullToRefresh) {

                                        options.timeout = 60000;

                                        angular.forEach(copy, function(uri) {
                                            provider.defaultCache.removeItem(uri);
                                        });

                                        provider.valueidCache.setItem(valueidCachekey, [cacheKey]);

                                    } else {
                                        if(copy.indexOf(cacheKey) === -1) {
                                            copy.push(cacheKey);
                                            provider.valueidCache.setItem(valueidCachekey, copy);
                                        }
                                    }

                                }

                            });

                    }

                    /**
                     * Check expiration date.
                     */
                    var Pages = $injector.get("Pages");
                    var touched = Pages.getForValueId(value_id);
                    var data_break = null;

                    if(touched.touched_at !== -1) {
                        options = angular.extend({}, options, {
                            touched_at: touched.touched_at,
                            expires_at: touched.expires_at
                        });
                    }

                    options = angular.extend({}, options, {
                        cacheKey            : cacheKey
                    });

                    provider.defaultCache
                        .getItem(cacheKey)
                        .then(function(cached_object) {

                            /** We need to cache the object */
                            if(cached_object === null) {

                                $log.debug("network: ", cacheKey);

                                options.return_response = true;
                                options.first_cache = true;

                                provider.queue.addFirst(options);

                            } else {

                                data_break = angular.copy(cached_object);

                                options.first_cache = false;

                                var will_refresh = false;

                                if(options.imageProxy) {

                                    angular.extend(cached_object, {
                                        expires_at  : options.expires_at,
                                        touched_at  : options.touched_at
                                    });

                                    provider.defaultCache.setItem(cacheKey, cached_object);

                                    options.network_promise.resolve(data_break.data);
                                    options.deferred_promise.resolve(data_break.data);

                                    return;

                                } else {


                                    if(cached_object.expires_at === -1) {

                                        if(options.touched_at > cached_object.touched_at) {
                                            $log.debug("content is newer, we will refresh it.",
                                                options.touched_at, " > ", cached_object.touched_at, cacheKey);

                                            will_refresh = true;
                                        } else {
                                            $log.debug("content will not be refreshed, already up-to-date.",
                                                cached_object);

                                            will_refresh = true;
                                        }
                                    } else {

                                        var current_time = Math.trunc((new Date()).getTime()/1000);
                                        if(cached_object.expires_at < current_time) {
                                            $log.debug("cache expired, renewing ",
                                                cached_object.expires_at, " < ", current_time, cacheKey);

                                            will_refresh = true;
                                        } else {
                                            $log.debug("cache is not expired, won't refresh.", cached_object);

                                            will_refresh = true;
                                        }
                                    }

                                }

                                if(will_refresh || options.pullToRefresh) {

                                    if(options.pullToRefresh) {

                                        // increase timeout for refresh
                                        options.timeout = 60000;

                                        // direct handle for pull to refresh
                                        provider.queue.globalPause();
                                        options.return_response = true;
                                        provider.handleRequest(options);
                                        options.deferred_promise.promise
                                            .then(function() {
                                                // restart queue when done
                                                provider.queue.globalStart();
                                            });
                                    } else {
                                        options.return_response = false;
                                        provider.queue.add(options);
                                    }

                                }

                                if(!options.pullToRefresh) {

                                    options.deferred_promise.resolve(data_break.data);
                                }

                            }

                        }).catch(function(error) {

                            /** Reject with a standardized object response. */
                            options.deferred_promise.reject({
                                error       : true,
                                message     : $translate.instant("The given resource is not reachable") + " " + cacheKey,
                                exception   : error
                            });

                        });

                    console.log("options.deferred_promise", options.deferred_promise);

                    return options.deferred_promise.promise;
                };

                /**
                 * GET Request helper/builder
                 *
                 * @param url
                 * @param config
                 */
                httpWrapper.get = function(url, config) {

                    /** Build url automatically, doesn't need to require it from every factory */
                    if(url.indexOf("http") !== 0) {
                        url = Url.get(url, _.get(config, "urlParams", {}));
                    }

                    // Disable refresh if App is offline.
                    if($rootScope.isOffline) {
                        if(config === undefined) {
                            config = {
                                refresh: false
                            };
                        } else {
                            config.refresh = false;
                        }
                    }

                    return httpWrapper(_.extend({
                        method          : "GET",
                        url             : url,
                        cache           : !$rootScope.isOverview,
                        responseType    : "json"
                    }, config || {}));
                };

                /**
                 * POST Request helper/builder
                 *
                 * @param url
                 * @param config
                 */
                httpWrapper.post = function(url, config) {

                    /** Build url automatically, doesn't need to require it from every factory */
                    if(url.indexOf("http") !== 0) {
                        url = Url.get(url, _.get(config, "urlParams", {}));
                    }

                    // Disable refresh if App is offline.
                    if($rootScope.isOffline) {
                        if(config === undefined) {
                            config = {
                                refresh: false
                            };
                        } else {
                            config.refresh = false;
                        }
                    }

                    return httpWrapper(_.extend({
                        method          : "POST",
                        url             : url,
                        cache           : !$rootScope.isOverview,
                        responseType    : "json",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded; charset=utf-8"
                        },
                    }, config || {}));
                };

                httpWrapper.head    = $http.head;
                httpWrapper.put     = $http.put;
                httpWrapper.delete  = $http.delete;
                httpWrapper.jsonp   = $http.jsonp;
                httpWrapper.patch   = $http.patch;

                // This needs to be dynamic because postForm is an angular decorator
                Object.defineProperty(httpWrapper, "postForm", {
                    get: function() {
                        return $http.postForm;
                    }
                });

                httpWrapper.cache = function(uri, config) {

                    return httpWrapper.get(uri, {
                        cache: !$rootScope.isOverview
                    });
                };

                /**
                 * Images uris are unique, if an image changes, so the uri will,
                 * Image will expire automatically 7 days after being touched.
                 *
                 * @param uri
                 */
                httpWrapper.cacheImage = function(uri) {

                    if(isNativeApp && window.OfflineMode) {
                        return $q(function(resolve, reject) { window.OfflineMode.cacheURL(uri, function() {
                            $log.info("cached URL succesfully : ", uri);
                            resolve();
                        }, function() {
                            $log.info("Failed to cache URL : ", uri);
                            reject();
                        }); });
                    }

                    return $q.reject();
                };

                /**
                 * Cache raw data, key/data pairs
                 *
                 * @param key
                 * @param data
                 */
                httpWrapper.getItem = function (key, dataCallback) {
                    if (typeof dataCallback !== 'function') {
                        throw new Error('dataCallback must be a function.');
                    }

                    var deferred = $q.defer();

                    provider.registryCache
                        .getItem(key)
                        .then(function (cachedData) {
                            // We need to cache the object!
                            if (cachedData === null) {
                                provider.registryCache
                                    .setItem(key, dataCallback.call(this))
                                    .then(function (resolve) {
                                        deferred.resolve(resolve);
                                    }, function (reject) {
                                        deferred.reject(reject);
                                    });
                            } else {
                                provider.registryCache
                                    .setItem(key, dataCallback.call(this));

                                deferred.resolve(cachedData);
                            }
                        }).catch(function (error) {
                            // Reject with a standardized object response!
                            deferred.reject(null);
                        });

                    return deferred.promise;
                };

                /**
                 * Reject wrapper standardized
                 *
                 * @param message
                 * @returns {Promise}
                 */
                httpWrapper.reject = function(message) {
                    message = (typeof message !== "undefined") ?
                        message : $translate.instant("The request is probably missing some parameters.");

                    /** Reject with a standardized object response. */
                    return $q.reject({
                        error       : true,
                        message     : message
                    });
                };

                /**
                 * Resolve wrapper
                 *
                 * @returns {Promise}
                 */
                httpWrapper.resolve = function(data) {
                    return $q.resolve(data);
                };

                /**
                 * Defer wrapper
                 *
                 * @returns {Promise}
                 */
                httpWrapper.defer = function() {
                    return $q.defer();
                };

                /**
                 * Alias for Pages, avoid circular deps
                 *
                 * @param value_id
                 * @returns {*}
                 */
                httpWrapper.getPayloadForValueId = function(value_id) {
                    var Pages = $injector.get("Pages");

                    return Pages.getPayloadForValueId(value_id);
                };

                /**
                 * Will clear all caches related to a given value_id (pull to refresh, invalidation)
                 *
                 * @param value_id
                 */
                httpWrapper.clearValueId = function(value_id) {

                    var deferred = $q.defer();

                    provider.valueidCache.getItem("valueid_" + value_id)
                        .then(function(cached_uris) {

                            var copy = angular.copy(cached_uris);

                            if(copy !== null) {
                                angular.forEach(copy, function(uri) {
                                    provider.defaultCache.removeItem(uri);
                                });
                            }

                            provider.valueidCache.removeItem("valueid_" + value_id)
                                .then(function () {
                                    deferred.resolve();
                                });

                        }).catch(function() {
                            deferred.resolve();
                        });

                    return deferred.promise;
                };

                httpWrapper.removeCached = function(uri) {
                    return $pwaCache.getDefaultCache().removeItem(uri);
                };

                return httpWrapper;
            }

            return new httpCacheLayer(httpCacheLayerConfig);
        }
    ];

    return provider;
});

/** $sbhttp Backward compatibility with pre 5.0 versions (mainly modules) */
angular.module("starter").provider("$sbhttp",function(){var b={alwaysCache:!1,neverCache:!1,debug:!1};return b.$get=["$rootScope","$http","$log","$q","$window","_",function(c,d,e,f,g,h){function k(a){var b=function(b){var g=h.upperCase(h.trim(h.get(b,"method"))),i=h.trim(h.get(b,"url"));if(e.debug(new Error("Stacktrace following").stack),"GET"===g&&i.length>0){e.debug("GET "+i);var k=c.isOnline,l=!a.neverCache||a.alwaysCache,m=h.get(b,"cache",l),n=[angular.noop],o=[angular.noop],p=!1,q=!1,r=f(function(a,c){var f=function(f){try{for(;h.isString(f)&&h.trim(f).length>0;)f=JSON.parse(f)}catch(a){e.info("Error parsing data :",a,data),f=null}var g=m&&f,l=h.extend({},b,{}),r=function(b){if(e.debug("Processing http response ("+i+") with status code "+h.get(b,"status")),h.isObject(b)&&0===b.status&&h.isObject(g))return k=!1,e.debug("request failed for "+i+": using cache"),r(g);g=b,p=!0,q=!(g.status>=200&&g.status<=299);var d=q?o:n,f=q?c:a,s=function(){h.isFunction(g.headers)&&"true"===g.headers("X-From-Native-Cache")&&(g=h.extend({},g,{fromCache:!0})),h.forEach(d,function(a){a(g.data,g.status,g.headers,l)}),f(g)};if(h.isObject(g)&&(k||!0!==g.fromCache)&&m&&!q){e.debug("caching response for URL "+i+" and status "+h.get(b,"status"));var t=JSON.stringify(h.extend({},g,{fromCache:!0}));try{t=JSON.stringify(t)}catch(a){e.info("Error stringifying data :",a,t)}return j.setItem(i,t).then(s,function(a){e.debug("LOCAL FORAGE ERROR : ",a),s()})}return s()};h.isObject(g)&&!k?(e.debug("we're offline: using cache"),r(g)):(e.debug("sending http call with config: ",l),d(l).then(r,r))};j.getItem(i).then(f,function(a){return e.debug("Error retrieving data from cache data :",a),f(null)})});return r.success=function(a){return h.isFunction(a)&&n.push(a),p&&!q&&a(response.data,response.status,response.headers,config),r},r.error=function(a){return h.isFunction(a)&&o.push(a),p&&q&&a(response.data,response.status,response.headers,config),r},r}return d(b)};return b.get=function(a,c){return b(h.extend({},c||{},{method:"GET",url:a}))},b.head=d.head,b.post=d.post,b.put=d.put,b.delete=d.delete,b.jsonp=d.jsonp,b.patch=d.patch,Object.defineProperty(b,"postForm",{get:function(){return d.postForm}}),b.cache=function(a){return!c.is_webview&&window.OfflineMode?f(function(b,c){window.OfflineMode.cacheURL(a,function(){e.info("cached URL succesfully : ",a),b()},function(){e.info("Failed to cache URL : ",a),c()})}):f.reject()},b.removeCached=function(a){return j.removeItem(a)},b}var j,i={alwaysCache:b.alwaysCache,neverCache:!b.alwaysCache&&b.neverCache,debug:!0===b.debug};return j={},j.getItem=j.setItem=j.removeItem=function(){return f.reject("no offline mode cache in webview")},(ionic.Platform.isIOS()||ionic.Platform.isAndroid())&&window.localforage&&(window.localforage.config({name:"sb-offline-mode",storeName:"keyvaluepairs",size:262144e3}),j=window.localforage),new k(i)}],b});
/** httpCache Backward compatibility with pre 5.0 versions (mainly modules) */
angular.module("starter").service("httpCache", function($sbhttp, $cacheFactory, ConnectionService) {return {remove: function(url) {var sid = localStorage.getItem("sb-auth-token");if(sid && url.indexOf(".html") == -1 && ConnectionService.isOnline) {url = url + "?sb-token=" + sid;}if(angular.isDefined($cacheFactory.get('$http').get(url))) {$cacheFactory.get('$http').remove(url);}$sbhttp.removeCached(url);return this;}};});