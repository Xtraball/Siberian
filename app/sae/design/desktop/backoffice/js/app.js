var App = angular.module("Siberian-Backoffice", ['ngRoute', 'ngAnimate', 'ngSanitize', 'angularFileUpload', 'angular-sortable-view', 'ng.ckeditor', 'chart.js', 'bgf.paginateAnything', 'ng-phpdebugbar', 'ngQueue']);
var meta;

App.run(function($rootScope, $window, $route, $location, Message, AUTH_EVENTS, Auth) {

    $rootScope.message = new Message();

    $window.Auth = Auth;
    $rootScope.logout = function() {
        Auth.logout().success(function() {
            $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
            $location.path("/backoffice");
        });
    };

    $rootScope.$on(AUTH_EVENTS.notAuthenticated, function() { $rootScope.isLoggedIn = false; });
    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() { $rootScope.isLoggedIn = false; });
    $rootScope.$on(AUTH_EVENTS.loginSuccess, function() { $rootScope.isLoggedIn = true; });

    $rootScope.$on('$locationChangeStart', function(event) {
        $rootScope.actualLocation = $location.path();
    });

    $rootScope.$watch(function () {return $location.path()}, function (newLocation, oldLocation) {
        if($rootScope.actualLocation === newLocation) {
            $rootScope.direction = 'to-right';
        } else {
            $rootScope.direction = 'to-left';
        }
    });

    $rootScope.$on('$routeChangeStart', function(event, current) {

        if (!Auth.isLoggedIn()) {
            event.preventDefault();
            $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
        }

    });

    $rootScope.$on('$routeChangeSuccess', function(event, current) {
        $rootScope.code = current.code;
    });

}).config(function($locationProvider, $compileProvider, $httpProvider) {
    $locationProvider.html5Mode(true);
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|geo|tel):/);
    $httpProvider.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

    $httpProvider.interceptors.push(function ($q, $injector) {
        return {
            response: function(response) {
                // Wrapper for common meta data, avoiding long polling.
                if(angular.isObject(response.data) && angular.isDefined(response.data.meta)) {
                    meta = response.data.meta;
                    if(angular.isObject(meta) && angular.isDefined(meta.unread_messages)) {
                        var unread_mess = document.getElementById('unread_messages');
                        if(angular.isObject(unread_mess) && angular.isDefined(unread_mess.style)) {
                            if(meta.unread_messages <= "0") {
                                unread_mess.style = "display: none;";
                            } else {
                                unread_mess.style = "display: initial;";
                            }

                            angular.element(unread_mess).text(meta.unread_messages);
                        }
                    }

                    // Clean-up meta
                    delete response.data.meta;
                }

                return response;
            }
        };
    });
});