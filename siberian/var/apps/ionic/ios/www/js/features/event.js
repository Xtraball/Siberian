/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('event-list', {
            url: BASE_PATH + '/event/mobile_list/index/value_id/:value_id',
            controller: 'EventListController',
            templateUrl: 'templates/event/l1/list.html',
            cache: false,
            resolve: lazyLoadResolver('event')
        }).state('event-view', {
            url: BASE_PATH + '/event/mobile_view/index/value_id/:value_id/event_id/:event_id',
            controller: 'EventViewController',
            templateUrl: 'templates/event/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('event')
        }).state('event-map', {
            url: BASE_PATH + '/event/mobile_map/index/value_id/:value_id/event_id/:event_id',
            templateUrl: 'templates/html/l1/maps.html',
            controller: 'EventMapController',
            cache: false,
            resolve: lazyLoadResolver('event')
        });
});
