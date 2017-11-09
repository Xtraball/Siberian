/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('twitter-list', {
            url: BASE_PATH + '/twitter/mobile_twitter_list/index/value_id/:value_id',
            controller: 'TwitterListController',
            templateUrl: 'templates/twitter/l1/list.html',
            cache: false,
            resolve: lazyLoadResolver('twitter')
        });
});
