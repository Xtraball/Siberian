"use strict";

App.service('LayoutService', function ($window, $rootScope, $timeout, $q, $location, $routeParams, Application, Padlock, Pages, Customer, AUTH_EVENTS, Url) {

    var self = this;

    this._initData = function() {

        this.data = null;
        this.need_to_build_the_options = true;
        this.options = null;
        this.is_initialized = false;
        this.dataLoading = false;

        this.properties = {
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
                 * bottom: menu displayed in bottom side
                 */
                position: 'normal',
                /**
                 * === visibility ===
                 * home: menu displayed in home page (used in combination with position 'normal')
                 * toggle: menu visibility is togglable with a button
                 * always: menu is always visible
                 */
                visibility: 'homepage',
                // status of menu visibility (used in 'toggle' mode only)
                isVisible: false
            }
        };

    };

    this.getData = function () {
        var deffered = $q.defer();

        // filter features
        if (self.data === null) {

            if (self.dataLoading) {
                // already loading
                $timeout(function () {
                    deffered.resolve(self.getData());
                }, 200);
            } else {

                self.dataLoading = true;

                // load data
                Pages.findAll().success(function(data) {

                    self.data = data;

                    self.properties.layoutId = data.layout_id;
                    self.properties.tabbar_is_transparent = data.tabbar_is_transparent;

                    self._init();

                    deffered.resolve(self.data);

                }).error(function (err) {
                    deffered.reject(err);
                }).finally(function () {
                    self.dataLoading = false;
                });

            }

        } else {
            if(self.need_to_build_the_options) {
                self._buildOptions();
            }

            deffered.resolve(self.data);
        }

        return deffered.promise;
    };

    this.getOptions = function () {

        var deffered = $q.defer();

        // filter features

        if (self.options === null) {

            self.getData().then(function (data) {

                deffered.resolve(self.options);

            }, function (err) {
                deffered.reject(err);
            });
        } else {
            deffered.resolve(self.options);
        }

        return deffered.promise;
    };

    this.getActiveOptions = function () {

        var deffered = $q.defer();

        self.getOptions().then(function (options) {

            // filter active options
            var options = options.reduce(function (options, option) {
                if (option.is_active == 1) {
                    options.push(option);
                }
                return options;
            }, []);

            self.data.customer_account.url = Customer.isLoggedIn() ? self.data.customer_account.edit_url : self.data.customer_account.login_url;
            self.data.customer_account.path = Customer.isLoggedIn() ? self.data.customer_account.edit_path : self.data.customer_account.login_path;

            if (self.data.customer_account.is_visible) {
                options.push(self.data.customer_account);
            }

            deffered.resolve(options);
        });
        return deffered.promise;

    };

    this.getFeatures = function () {

        var deffered = $q.defer();

        self.getActiveOptions().then(function (options) {

            /*
             * features
             * - features.layoutId: layout id
             * - features.options: filtered options, including customer_account if visible
             * - features.overview: filtered options truncated if > to data.limit_to, and concatenated with more_items
             */
            var features = {
                layoutId: self.data.layout_id,
                options: options,
                overview: {
                    hasMore: false,
                    options: [],
                    paged_options: [],
                    limit: self.data.limit_to
                },
                data: self.data
            };

            var limit = features.overview.limit;

            if (limit !== null && limit > 0 && features.options.length > limit) {

                if(self.data.layout.use_horizontal_scroll) {

                    var paged_options = [];
                    for (var i = 0; i < features.options.length; i++) {
                        paged_options.push(features.options[i]);
                        if(((i+1) % limit) == 0) {
                            features.overview.paged_options.push(paged_options);
                            paged_options = [];
                        }
                    }
                    if(paged_options.length) {
                        features.overview.paged_options.push(paged_options);
                    }

                } else {

                    // truncate to (limit - 1)
                    for (var i = 0; i < (limit - 1); i++) {
                        features.overview.options.push(features.options[i]);
                    }

                    features.overview.hasMore = true;

                }
            } else if(self.data.layout.use_horizontal_scroll) {
                features.overview.paged_options = [features.options];
            } else {
                features.overview.options = features.options;
            }

            if(self.data.layout_id == "l8") {
                features = self.initLayout8(features);
            }

            //if(!Application.is_loaded && Application.device_uid) {
            //    Application.setDeviceUid(Application.device_uid);
            //}

            Application.isLoaded();

            deffered.resolve(features);
        });

        return deffered.promise;

    };

    this._updateFromUrl = function (url) {

        if (self.options === null) {
            return;
        }

        if ($routeParams.value_id) {

            var optionId = parseInt($routeParams.value_id);

            // get current option from URL
            var currentOption = self.options.reduce(function (currentOption, option) {

                if (option.id === optionId) {

                    if (option.url === url) {
                        self.properties.options.isRootPage = true;
                        self.properties.menu.isVisible = self.properties.menu.visibility === 'always';
                    } else {
                        self.properties.options.isRootPage = false;
                        self.properties.menu.isVisible = self.properties.menu.visibility === 'always' || false;
                    }

                    currentOption = option;
                }
                return currentOption;
            }, null);

            if (currentOption === null) {
                self.properties.options.isRootPage = false;
            } else if (self.properties.options.current !== currentOption) {
                self.properties.options.current = currentOption;
            }

            //var currentOptionName = self.properties.options.current ? self.properties.options.current.name : '-';
            //console.debug('Current option: %s (root = %b)', currentOptionName, self.properties.options.isRootPage);

            return currentOption;
        } else {

            if(/customer\/mobile_account/.test(url) || /cms\/mobile_privacypolicy/.test(url)) {

                self.properties.options.isRootPage = /customer\/mobile_account_login/.test(url);
                self.properties.menu.isVisible = self.properties.menu.visibility === 'always';
                self.properties.options.current = null;

            } else {
                return null;
            }
        }
    };

    this.fireLocationChanged = function () {
        self._updateFromUrl($location.absUrl());
    };

    this._init = function () {

        var options = self._buildOptions();

        self.properties.menu.position = self.data.layout.position;
        self.properties.menu.visibility = self.data.layout.visibility;
        self.properties.options.autoSelectFirst = self.properties.menu.visibility != "homepage";

        Application.options = options;
        Application.layout_visibility = self.data.layout.visibility;

        this._updateFromUrl($location.absUrl());

        self.is_initialized = true;
    };

    this.initLayout8 = function (features) {

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

    this._buildOptions = function () {

        self.options = self.data.pages.reduce(function (options, option) {

            if (!option.is_locked || Customer.can_access_locked_features || Padlock.unlock_by_qrcode) {
                // option is not locked for user

                if ( (!Customer.isLoggedIn() && !Padlock.unlock_by_qrcode) || option.code != "padlock") {
                    // use is logged or not padlock feature
                    if((Application.handle_code_scan && option.code == "code_scan") || option.code != "code_scan") {
                        options.push(option);
                    }
                }
            }
            return options;

        }, []);

        self.need_to_build_the_options = false;
        return self.options;
    };

    this.setLayoutId = function(value_id, layout_id) {
        for(var i in self.options) {
            if(self.options[i].value_id == value_id) {
                self.options[i].layout_id = layout_id;
            }
        }
    };

    this._initData();

    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() { self.need_to_build_the_options = true; });
    $rootScope.$on(AUTH_EVENTS.loginSuccess, function() { self.need_to_build_the_options = true; });

    return {
        leftAreaSize: 150,
        getData: function () {
            return self.getData();
        },
        unsetData: function() {
            self._initData();
            $rootScope.$broadcast("tabbarStatesChanged");
        },
        getOptions: function () {
            return self.getOptions();
        },
        getFeatures: function () {
            return self.getFeatures();
        },
        isInitialized: function() {
            return self.is_initialized;
        },
        unlockByQRCode: function(qrcode) {
            return self.unlockByQRCode(qrcode)
        },
        setNeedToBuildTheOptions: function(need_to_build_the_options) {
            self.need_to_build_the_options = need_to_build_the_options;
        },
        rebuildOptions: function() {
            self._buildOptions();
        },
        setLayoutId: function(value_id, layout_id) {
            self.setLayoutId(value_id, layout_id);
        },
        properties: self.properties,
        app: {
            // trigger event from app root
            fireLocationChanged: self.fireLocationChanged
        }
    };

});