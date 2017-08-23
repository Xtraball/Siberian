/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('video-list', {
            url: BASE_PATH + '/media/mobile_gallery_video_list/index/value_id/:value_id',
            controller: 'VideoListController',
            templateUrl: 'templates/media/video/l1/list.html',
            cache: false,
            resolve: lazyLoadResolver(['youtube', 'video'])
        });
});
