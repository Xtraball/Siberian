/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('links-view', {
            url: BASE_PATH + '/weblink/mobile_multi/index/value_id/:value_id',
            controller: 'LinksViewController',
            templateUrl: 'templates/links/l1/view.html',
            code: 'weblink',
            cache: false,
            resolve: lazyLoadResolver('links')
        });
});
