/*global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module("starter").config(function ($stateProvider, $urlRouterProvider) {

    $stateProvider
        .state("home", {
            url             : BASE_PATH,
            templateUrl     : "templates/home/view.html",
            controller      : "HomeController",
            cache           : false,
            resolve         : lazyLoadResolver("homepage"),
            onEnter         : ["HomepageLayout", "$rootScope", function(HomepageLayout, $rootScope) {
                if (HomepageLayout.properties.options.autoSelectFirst) {
                    $rootScope.app_hide_navbar = false;
                } else {
                    $rootScope.app_hide_navbar = true;
                }
            }]
        });

    $urlRouterProvider.otherwise(BASE_PATH);

});