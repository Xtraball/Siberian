"use strict";

App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/media/mobile_gallery_music_playlistalbums/index/value_id/:value_id/playlist_id/:playlist_id", {
        controller: 'MediaGalleryMusicPlaylistAlbumsController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_playlistalbums/template",
        code: "media-gallery-music-page media-gallery-music-playlist-albums"
    });

}).controller('MediaGalleryMusicPlaylistAlbumsController', function ($window, $scope, $routeParams, $location, Url, MediaMusicPlaylist, MediaMusicAlbum, MediaMusicTracksLoaderService, MediaMusicPlayerService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = $routeParams.value_id;
    MediaMusicPlaylist.value_id = $routeParams.value_id;
    MediaMusicAlbum.value_id = $routeParams.value_id;

    $scope.loadContent = function () {
        MediaMusicPlaylist.find($routeParams.playlist_id).success(function (data) {

            $scope.playlist = data.playlist;

            MediaMusicAlbum.findByPlaylist($routeParams.playlist_id).success(function (data) {

                $scope.playlist.albums = data.albums;

            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    }

    $scope.showAlbum = function (album) {
        $location.path(album.path);
    };

    $scope.showPlaylistTracks = function () {
        $location.path(Url.get("media/mobile_gallery_music_playlisttracks/index", {
            value_id: $routeParams.value_id,
            playlist_id: $routeParams.playlist_id
        }));
    };

    $scope.playAll = function () {

        $scope.is_loading = true;

        MediaMusicTracksLoaderService.loadTracksFromAlbums($scope.playlist.albums).then(function (results) {

            if(Application.handle_audio_player) {

                $window.audio_player_data = JSON.stringify(
                    {
                        tracks: results.tracksLoader.tracks,
                        albums: $scope.playlist.albums,
                        trackIndex: 0
                    }
                );
                Application.call("openAudioPlayer", $window.audio_player_data);

            }else{

                MediaMusicPlayerService.init(document);
                // play all tracks (starting from first one)
                MediaMusicPlayerService.playTracks(results.tracksLoader, 0);

            }

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});