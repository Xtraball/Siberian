"use strict";

App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/media/mobile_gallery_music_playlisttracks/index/value_id/:value_id/playlist_id/:playlist_id", {
        controller: 'MediaGalleryMusicPlaylistTracksController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_playlisttracks/template",
        code: "media-gallery-music-page media-gallery-music-playlist-tracks"
    });

}).controller('MediaGalleryMusicPlaylistTracksController', function ($scope, $routeParams, $location, $window, $q, Url, MediaMusicPlaylist, MediaMusicAlbum, MediaMusicTrack, MediaMusicTracksLoaderService, MediaMusicPlayerService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.is_loading_more_tracks = false;

    $scope.value_id = $routeParams.value_id;
    MediaMusicPlaylist.value_id = $routeParams.value_id;
    MediaMusicAlbum.value_id = $routeParams.value_id;
    MediaMusicTrack.value_id = $routeParams.value_id;

    $scope.loadContent = function () {

        MediaMusicPlaylist.find($routeParams.playlist_id).success(function (data) {

            $scope.playlist = data.playlist;

            MediaMusicAlbum.findByPlaylist($routeParams.playlist_id).success(function (data) {

                MediaMusicTracksLoaderService.loadTracksFromAlbums(data.albums).then(function (results) {

                    $scope.tracksLoader = results.tracksLoader;
                    $scope.tracks = results.tracksLoaded;

                }).finally(function () {
                    $scope.is_loading = false;
                });

            }, function () {
                // error
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.showPlaylistAlbums = function () {
        $window.history.back();
    };

    $scope.play = function ($trackIndex) {

        if(Application.handle_audio_player) {

            $window.audio_player_data = JSON.stringify(
                {
                    tracks: $scope.tracksLoader.tracks,
                    albums: $scope.tracksLoader.albums,
                    trackIndex: $trackIndex
                }
            );
            Application.call("openAudioPlayer", $window.audio_player_data);

        }else{

            MediaMusicPlayerService.init(document);
            // play all tracks (starting from first one)
            MediaMusicPlayerService.playTracks($scope.tracksLoader, $trackIndex);

        }

    };

    $scope.enable_load_onscroll = true;

    $scope.loadMore = function () {
        if ($scope.tracksLoader) {
            $scope.is_loading_more_tracks = true;
            return $scope.tracksLoader.loadMore(50).then(function (results) {
                // add more tracks    
                $scope.tracks = $scope.tracks.concat(results.tracksLoaded);
                $scope.is_loading_more_tracks = false;
            });
        }
    };

    $scope.loadContent();

});