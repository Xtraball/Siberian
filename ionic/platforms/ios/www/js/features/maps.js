/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('maps-view', {
            url: BASE_PATH + '/maps/mobile_view/index/value_id/:value_id',
            controller: 'MapsController',
            templateUrl: 'templates/maps/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('maps')
        });
});
