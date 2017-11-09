/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider.state('media-player', {
        url: BASE_PATH + '/media/mobile_gallery_music_player/index/value_id/:value_id',
        controller: 'MediaPlayerController',
        templateUrl: 'templates/media/music/l1/player/view.html',
        resolve: lazyLoadResolver('media')
    });
});
