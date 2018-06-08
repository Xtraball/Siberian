/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('topic-list', {
            url: BASE_PATH + '/topic/mobile_list/index/value_id/:value_id',
            controller: 'TopicController',
            templateUrl: 'templates/topic/l1/list.html',
            cache: false,
            resolve: lazyLoadResolver('topic')
        });
});
