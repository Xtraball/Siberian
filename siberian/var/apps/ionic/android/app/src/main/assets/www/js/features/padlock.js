/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('padlock-view', {
            url: BASE_PATH + '/padlock/mobile_view/index/value_id/:value_id',
            params: {
                value_id: 0
            },
            controller: 'PadlockController',
            templateUrl: 'templates/padlock/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('padlock')
        });
});
