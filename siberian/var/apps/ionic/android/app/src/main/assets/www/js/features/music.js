/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('music-playlist-list', {
            url: BASE_PATH + '/media/mobile_gallery_music_playlists/index/value_id/:value_id',
            controller: 'MusicPlaylistsController',
            templateUrl: 'templates/media/music/l1/playlist/list.html',
            cache: false,
            resolve: lazyLoadResolver('media')
        }).state('music-playlist-albums', {
            url: BASE_PATH + '/media/mobile_gallery_music_playlistalbums/index/value_id/:value_id/playlist_id/:playlist_id',
            controller: 'MusicPlaylistAlbumsController',
            templateUrl: 'templates/media/music/l1/playlist/albums.html',
            cache: false,
            resolve: lazyLoadResolver('media')
        }).state('music-album-list', {
            url: BASE_PATH + '/media/mobile_gallery_music_albums/index/value_id/:value_id',
            controller: 'MusicAlbumsListController',
            templateUrl: 'templates/media/music/l1/album/list.html',
            cache: false,
            resolve: lazyLoadResolver('media')
        }).state('music-album-view', {
            url: BASE_PATH + '/media/mobile_gallery_music_album/index/value_id/:value_id/album_id/:album_id',
            controller: 'MusicAlbumViewController',
            templateUrl: 'templates/media/music/l1/album/view.html',
            cache: false,
            resolve: lazyLoadResolver('media')
        }).state('music-album-view-track', {
            url: BASE_PATH + '/media/mobile_gallery_music_album/index/value_id/:value_id/track_id/:track_id',
            controller: 'MusicAlbumViewController',
            templateUrl: 'templates/media/music/l1/album/view.html',
            cache: false,
            resolve: lazyLoadResolver('media')
        }).state('music-track-list', {
            url: BASE_PATH + '/media/mobile_gallery_music_playlisttracks/index/value_id/:value_id/playlist_id/:playlist_id',
            controller: 'MusicTrackListController',
            templateUrl: 'templates/media/music/l1/track/list.html',
            cache: false,
            resolve: lazyLoadResolver('media')
        });
});
