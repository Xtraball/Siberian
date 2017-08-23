/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('weather-view', {
            url: BASE_PATH + '/weather/mobile_view/index/value_id/:value_id',
            controller: 'WeatherController',
            templateUrl: 'templates/weather/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('weather')
        });
});
