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
