/* global
 App, lazyLoadResolver, BASE_PATH, DOMAIN, DEVICE_TYPE
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('codescan', {
            url: BASE_PATH + '/codescan/mobile_view/index/value_id/:value_id',
            controller: 'CodeScanController',
            templateUrl: 'templates/html/l1/loading.html',
            cache: false,
            resolve: lazyLoadResolver('codescan')
        });
});
