var App = angular.module("Siberian-Backoffice", ['ngRoute', 'ngAnimate', 'ngSanitize', 'angularFileUpload', 'googlechart', 'angular-sortable-view', 'ng.ckeditor']);

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
    $httpProvider.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest"
});