/* global
 App, angular, lazyLoadResolver, BASE_PATH, DOMAIN, Message
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('cms-view', {
            url: BASE_PATH + '/cms/mobile_page_view/index/value_id/:value_id',
            controller: 'CmsViewController',
            templateUrl: 'templates/cms/page/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver(['cms', 'places'])
        })
        .state('cms-view-map', {
            url: BASE_PATH + '/cms/mobile_page_view_map/index/value_id/:value_id/page_id/:page_id/block_id/:block_id',
            params: {
                page_id: 0
            },
            controller: 'CmsViewMapController',
            templateUrl: 'templates/html/l1/maps.html',
            cache: false,
            resolve: lazyLoadResolver(['cms', 'places'])
        });
});
