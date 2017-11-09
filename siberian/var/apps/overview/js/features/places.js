/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('places-list', {
            url: BASE_PATH + '/places/mobile_list/index/value_id/:value_id',
            controller: 'PlacesListController',
            templateUrl: 'templates/html/l3/list.html',
            cache: false,
            resolve: lazyLoadResolver(['cms', 'places'])
        }).state('places-list-map', {
            url: BASE_PATH + '/cms/mobile_list_map/index/value_id/:value_id',
            controller: 'CmsListMapController',
            templateUrl: 'templates/html/l1/maps.html',
            cache: false,
            resolve: lazyLoadResolver(['cms', 'places'])
        }).state('places-view', {
            url: BASE_PATH + '/cms/mobile_page_view/index/value_id/:value_id/page_id/:page_id/type/:type',
            controller: 'CmsViewController',
            templateUrl: 'templates/cms/page/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver(['cms', 'places'])
        });
});
