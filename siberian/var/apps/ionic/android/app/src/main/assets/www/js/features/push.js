angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('push-list', {
            url: BASE_PATH + '/push/mobile_list/index/value_id/:value_id',
            controller: 'PushController',
            templateUrl: 'templates/html/l1/list.html',
            cache: false,
            resolve: lazyLoadResolver('push')
        });
});
