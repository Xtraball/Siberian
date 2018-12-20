/* global
 angular, console, BASE_PATH
 @version 4.15.7
 */
window.Features = (new (function Features() {
    var _app = angular.module('starter'); // WARNING: Must be the same as in app.js
    var $this = {};
    var __features = {};

    $this.featuresToLoadOnStart = [];

    $this.insertCSS = function (css_content, feature_code) {
        var css = document.createElement('style');
        css.type = 'text/css';
        css.setAttribute('data-feature', feature_code);
        css.innerHTML = css_content;
        document.head.appendChild(css);
    };

    /**
     * Check if a bundle is registered
     * @param code
     * @returns {boolean}
     */
    $this.isRegistered = function (code) {
        return __features.hasOwnProperty(code);
    };

    $this.register = function (json, bundle) {
        if (angular.isDefined(json.load_on_start) && json.load_on_start) {
            var onStart = {
                path: bundle
            };
            if (json.on_start_factory) {
                onStart.factory = json.on_start_factory
            }
            $this.featuresToLoadOnStart.push(onStart);
        }

        // Lazy load deps!
        var lazyLoadBundle = angular.copy(bundle);
        if (json.lazyLoad && json.lazyLoad.module) {
            lazyLoadBundle = lazyLoadBundle.concat(json.lazyLoad.module);
        }

        var feature_base = 'features/'+json.code+'/';
        _app.config(
            [
                '$stateProvider', 'HomepageLayoutProvider',
                function ($stateProvider, HomepageLayoutProvider) {
                    var template_base = feature_base + 'assets/templates/';
                    var routes = {};
                    angular.forEach(json.routes, function (r) {
                        if (r.autoregister !== false) {
                            var route = {
                                'url': BASE_PATH + '/' + r.url,
                                'controller': r.controller
                            };

                            if (angular.isDefined(lazyLoadBundle)) {
                                route.resolve = {
                                    lazy: ['$ocLazyLoad', function ($ocLazyLoad) {
                                        if (json.lazyLoad && json.lazyLoad.core) {
                                            json.lazyLoad.core.forEach(function (bundle) {
                                                $ocLazyLoad.load('./dist/packed/' + bundle + '.bundle.min.js')
                                            });
                                        }

                                        return $ocLazyLoad.load(lazyLoadBundle);
                                    }]
                                };
                            }

                            switch (true) {
                                case angular.isString(r.templateHTML):
                                        route.template = r.templateHTML;
                                    break;
                                case (angular.isObject(r.layouts) && angular.isString(r.template)):
                                        route.templateUrl = function (param) {
                                            // Override the layout_id for a specific route
                                            if (param.layout_id !== undefined && angular.isString(r.layouts[param.layout_id])) {
                                                return template_base + r.layouts[param.layout_id] + '/' + r.template;
                                            }

                                            // Try to get default layout for the feature otherwise!
                                            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                                            if (angular.isString(r.layouts[layout_id])) {
                                                return template_base + r.layouts[layout_id] + '/' + r.template;
                                            }

                                            // Fallback on base layout!
                                            return template_base + r.layouts.default + '/' + r.template;
                                        };
                                    break;
                                case ((r.externalTemplate === true) && angular.isString(r.template)):
                                        route.templateUrl = r.template;
                                    break;
                                case angular.isString(r.template):
                                        route.templateUrl = template_base + r.template;
                                    break;
                            }

                            // route.cache = false if r.cache === false, otherwise route.cache = true
                            route.cache = (r.cache !== false);

                            this[r.state] = route;
                        }
                    }, routes);

                    angular.forEach(routes, function (route, state) {
                        $stateProvider.state(state, route);
                    });
                }
            ]
        );

        __features[json.code] = json;
    };

    _app.config(['$stateProvider', function ($stateProvider) {
        $stateProvider
            .state('go-to-feature', {
                url: BASE_PATH + '/goto/feature/:code/value_id/:value_id',
                template: 'redirecting...'
            });
    }]).factory(
        'Features', [
            '$q', '$state',
            function ($q, $state) {
                var factory = {};

                factory.register = function () {
                    throw new Error('You cannot register new features at runtime, please use window.Features before angular app has bootstraped.');
                };


                // Get a feature JSON
                factory.get = function (feature_code) {
                    if (__features.hasOwnProperty(feature_code)) {
                        return __features[feature_code];
                    }

                    return null;
                };

                // Go to a feature root path
                factory.goTo = function (feature_code, value_id) {
                    var feature = factory.get(feature_code);

                    if (feature) {
                        var route = null;
                        for (var i = 0; i < feature.routes.length; i++) {
                            var r = feature.routes[i];
                            if (angular.isObject(r) && r.root) {
                                route = feature.routes[i];
                                break;
                            }
                        }
                        if (route) {
                            $state.go(route.state, {
                                value_id: value_id
                            });
                            $q.resolve();
                        }
                    }

                    return $q.reject;
                };

                return factory;
            }
        ]
    ).run(['$rootScope', 'Features', function ($rootScope, Features) {
        $rootScope.$on('$stateChangeStart', function (evt, toState, toParams, fromState, fromParams) {
            if (angular.isObject(toState) && (toState.name === 'go-to-feature')) {
                evt.preventDefault();
                if (
                    angular.isObject(toParams) &&
                    angular.isString(toParams.code) &&
                    (toParams.code.trim().length > 0) &&
                    !isNaN(+toParams.value_id) &&
                    (+toParams.value_id > 0)
                ) {
                    Features.goTo(toParams.code, toParams.value_id);
                }
            }
        });
    }]);

    return $this;
})());

/**
 * hexToRgb converter
 */
window.hexToRgb = function (hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
};

/**
 *  RGB To HEX color
 *
 * @param red
 * @param green
 * @param blue
 * @returns {*}
 */
window.rgbToHex = function (red, green, blue) {
    return '#' + ((1 << 24) + (red << 16) + (green << 8) + blue)
        .toString(16)
        .slice(1);
};

/**
 * Find statusbar style from RGB Color!
 * @param hex
 * @returns {string}
 */
window.textStyleFromHex = function (hex) {
    var statusBarStyle = 'light';
    var rgbColor = window.hexToRgb(hex);
    if (rgbColor.r * 0.299 + rgbColor.g * 0.587 + rgbColor.b * 0.114 > 186) {
        // Black!
        statusBarStyle = 'dark';
    }

    return statusBarStyle;
};

/**
 * Set statusbar color and take into accounts the text style
 * @param hex
 */
window.updateStatusBar = function (hex) {
    window.StatusBar.backgroundColorByHexString(hex);
    switch (window.textStyleFromHex(hex)) {
        case 'dark':
                window.StatusBar.styleDefault();
            break;
        case 'light':
        default:
            window.StatusBar.styleLightContent();
    }
};
