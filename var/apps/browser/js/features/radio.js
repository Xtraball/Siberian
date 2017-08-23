/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('radio', {
            url: BASE_PATH + '/radio/mobile_radio/index/value_id/:value_id',
            controller: 'RadioController',
            templateUrl: 'templates/html/l1/loading.html',
            cache: false,
            resolve: lazyLoadResolver(['media', 'radio'])
        });
});
