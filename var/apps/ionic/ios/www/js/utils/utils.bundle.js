/* global
 angular, console, BASE_PATH
 */
window.Features = (new (function Features() {
    var _app = angular.module('starter'); // WARNING: Must be the same as in app.js
    var $this = {};
    var __features = {};

    $this.insertCSS = function (css_content, feature_code) {
        var css = document.createElement('style');
        css.type = 'text/css';
        css.setAttribute('data-feature', feature_code);
        css.innerHTML = css_content;
        document.head.appendChild(css);
    };

    $this.register = function (json, bundle) {
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

                            if (angular.isDefined(bundle)) {
                                route.resolve = {
                                    lazy: ['$ocLazyLoad', function ($ocLazyLoad) {
                                        return $ocLazyLoad.load(bundle);
                                    }]
                                };
                            }

                            switch (true) {
                                case angular.isString(r.templateHTML):
                                        route.template = r.templateHTML;
                                    break;
                                case (angular.isObject(r.layouts) && angular.isString(r.template)):
                                        route.templateUrl = function (param) {
                                            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                                            if (angular.isString(r.layouts[layout_id])) {
                                                return template_base + r.layouts[layout_id] + '/' + r.template;
                                            }

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
                        console.log('creating state '+state+' with route', route);
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
;/*global
    App, angular
 */
angular.module("starter").config(['$provide', function($provide) {

    // Based on http://www.bennadel.com/blog/2615-posting-form-data-with-http-in-angularjs.htm
    // But we a really really more efficient version of serializeData
    var transformAsFormPost = function( data, getHeaders ) {
        function serializeData (data, prefix) {
            if(!angular.isString(prefix)) {
                prefix = "";
            }

            prefix = prefix.trim();

            if(angular.isArray(data) && (prefix.length < 1)) {
                prefix = "data";
            }


            var pairs = [];
            if(angular.isObject(data) || angular.isArray(data)) {
                var index = 0;
                angular.forEach(data, function (value, key) {
                    if(angular.isArray(data)) {
                        key = index;
                    }

                    key = encodeURIComponent(key);

                    if (prefix.length > 0) {
                        key = prefix + "[" + key + "]";
                    }

                    if (angular.isArray(value) || angular.isObject(value)) {
                        pairs.push(serializeData(value, key));
                    } else {
                        if (angular.isUndefined(value) || value === null) {
                            value = "";
                        }

                        pairs.push(key + "=" + encodeURIComponent(value));
                    }
                    index += 1;
                });
            }

            return pairs.join( "&" ).replace( /%20/g, "+" );
        }

        return( serializeData( data ) );
    };

    $provide.decorator('$http', [
        '$delegate', function($delegate) {

            $delegate.postForm = function(url, data, config) {
                if(!angular.isObject(config)) {
                    config = {};
                }

                if(!angular.isObject(config.headers)) {
                    config.headers = {};
                }

                config.headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";

                if(angular.isArray(config.transformRequest)) {
                    config.transformRequest.push(transformAsFormPost);
                }
                else if(angular.isFunction(config.transformRequest)) {
                    config.transformRequest = [config.transformRequest, transformAsFormPost];
                }
                else {
                    config.transformRequest = transformAsFormPost;
                }

                return $delegate.post(url, data, config);
            };

            return $delegate;
        }
    ]);

}]);
