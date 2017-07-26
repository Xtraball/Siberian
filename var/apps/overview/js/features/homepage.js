/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, $urlRouterProvider) {
    $stateProvider
        .state('home', {
            url: BASE_PATH,
            templateUrl: 'templates/home/view.html',
            controller: 'HomeController',
            cache: false,
            resolve: lazyLoadResolver('homepage')
            /**onEnter: ['HomepageLayout', '$rootScope', function (HomepageLayout, $ionicNavBarDelegate) {
                $ionicNavBarDelegate.showBar(HomepageLayout.properties.options.autoSelectFirst);
            }]*/
        });

    $urlRouterProvider.otherwise(BASE_PATH);
});
