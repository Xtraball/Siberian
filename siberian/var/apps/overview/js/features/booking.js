/* global
 angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('booking-view', {
            url: BASE_PATH + '/booking/mobile_view/index/value_id/:value_id',
            controller: 'BookingController',
            templateUrl: 'templates/booking/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('booking')
        });
});
