/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('locked', {
            url: BASE_PATH + '/locked/mobile_view/index',
            controller: 'LockedController',
            templateUrl: 'templates/locked/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('locked')
        });
});
