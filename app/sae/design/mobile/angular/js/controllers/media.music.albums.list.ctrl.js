"use strict";

App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/media/mobile_gallery_music_albums/index/value_id/:value_id", {
        controller: 'MediaGalleryMusicAlbumsController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_albums/template",
        code: "media-gallery-music-page media-gallery-music-albums"
    });

}).controller('MediaGalleryMusicAlbumsController', function ($scope, $routeParams, $location, $window, $q, Url,
    MediaMusicPlaylist, MediaMusicAlbum, MediaMusicTracksLoaderService, MediaMusicPlayerService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = MediaMusicAlbum.value_id = $routeParams.value_id;


    $scope.loadContent = function () {

        // retrieve albums
        MediaMusicAlbum.findAll().success(function (data) {

            $scope.albums = data.albums;

        }).finally(function () {
            $scope.is_loading = false;
        });
    }

    $scope.showPlaylists = function (playlist) {
        $window.history.back();
    };

    $scope.showAlbum = function (album) {
        $location.path(album.path);
    };

    $scope.playAll = function () {

        if ($scope.is_loading) return;

        $scope.is_loading = true;

        MediaMusicTracksLoaderService.loadTracksFromAlbums($scope.albums).then(function (results) {

            if(Application.handle_audio_player) {

                $window.audio_player_data = JSON.stringify(
                    {
                        tracks: results.tracksLoader.tracks,
                        albums: $scope.albums,
                        trackIndex: 0
                    }
                );
                Application.call("openAudioPlayer", $window.audio_player_data);

            } else {

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