/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('tip-view', {
            url: BASE_PATH + '/tip/mobile_view/index/value_id/:value_id',
            controller: 'TipController',
            templateUrl: 'templates/tip/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('tip')
        });
});
