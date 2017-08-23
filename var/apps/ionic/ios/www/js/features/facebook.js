/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('facebook-list', {
            url: BASE_PATH + '/social/mobile_facebook_list/index/value_id/:value_id',
            controller: 'FacebookListController',
            templateUrl: 'templates/html/l1/list.html',
            resolve: lazyLoadResolver('facebook'),
            cache: false
        }).state('facebook-view', {
            url: BASE_PATH + '/social/mobile_facebook_view/index/value_id/:value_id/post_id/:post_id',
            controller: 'FacebookViewController',
            templateUrl: 'templates/facebook/l1/view.html',
            resolve: lazyLoadResolver('facebook'),
            cache: false
        });
});
