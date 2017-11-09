angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('colors-view', {
            url: BASE_PATH + '/application/mobile_customization_colors/',
            controller: 'ApplicationColorsController',
            templateUrl: 'templates/application/l1/colors/view.html',
            resolve: lazyLoadResolver('application'),
            cache: false
        })
        .state('tc-view', {
            url: BASE_PATH + '/application/mobile_tc_view/index/tc_id/:tc_id',
            controller: 'ApplicationTcController',
            templateUrl: 'templates/application/l1/tc/view.html',
            resolve: lazyLoadResolver('application'),
            cache: false
        });
});
