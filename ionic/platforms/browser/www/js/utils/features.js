window.Features = {
    registry: [],
    features: {},
    featuresToLoadOnStart: [],
    insertCSS: function (cssContent, featureCode) {
        var css = document.createElement("style");
        css.setAttribute("type", "text/css");
        css.setAttribute("data-feature", featureCode);
        css.innerHTML = cssContent;
        document.head.appendChild(css);
    },
    register: function (json, bundle) {
        this.registry.push({
            json: json,
            bundle: bundle
        });
    },
    createStates: function ($stateProvider, json, bundle) {
        if (angular.isDefined(json.load_on_start) && json.load_on_start) {
            var onStart = {
                path: bundle
            };
            if (json.on_start_factory) {
                onStart.factory = json.on_start_factory;
            }
            this.featuresToLoadOnStart.push(onStart);
        }

        // Lazy load deps!
        var lazyLoadBundle = angular.copy(bundle);
        if (json.lazyLoad && json.lazyLoad.module) {
            lazyLoadBundle = lazyLoadBundle.concat(json.lazyLoad.module);
        }

        var feature_base = 'features/'+json.code+'/';

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
                        lazy: ["$ocLazyLoad", "$injector", "$q", function ($ocLazyLoad, $injector, $q) {
                            var deferred = $q.defer();
                            var _bundles = [];
                            if (json.lazyLoad && json.lazyLoad.core) {
                                json.lazyLoad.core.forEach(function (_bundle) {
                                    _bundles.push("./dist/packed/" + _bundle + ".bundle.min.js");
                                });
                            }

                            _bundles.push(lazyLoadBundle);

                            $ocLazyLoad
                                .load(_bundles)
                                .then(function () {
                                    deferred.resolve();
                                });

                            return deferred.promise;
                        }]
                    };
                }

                switch (true) {
                    case angular.isString(r.templateHTML):
                        route.template = r.templateHTML;
                        break;
                    case (angular.isObject(r.layouts) &&
                        angular.isString(r.template)):
                        route.templateUrl = function (param) {
                            // Override the layout_id for a specific route
                            if (param.layout_id !== undefined && angular.isString(r.layouts[param.layout_id])) {
                                return template_base + r.layouts[param.layout_id] + '/' + r.template;
                            }

                            // Fallback on base layout!
                            return template_base + r.layouts.default + '/' + r.template;
                        };
                        break;
                    case ((r.externalTemplate === true) &&
                        angular.isString(r.template)):
                        route.templateUrl = r.template;
                        break;
                    case angular.isString(r.template):
                        route.templateUrl = template_base + r.template;
                        break;
                }
                route.cache = (r.cache !== false);

                this[r.state] = route;
            }
        }, routes);

        angular.forEach(routes, function (route, state) {
            console.log("route", route);
            $stateProvider.state(state, route);
        });

        this.features[json.code] = json;
    }
};

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
