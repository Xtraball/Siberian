"use strict";

App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/media/mobile_gallery_music_album/index/value_id/:value_id/album_id/:album_id", {
        controller: 'MediaGalleryMusicAlbumController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_album/template",
        code: "media-gallery-music-page media-gallery-music-album"
    }).when(BASE_URL + "/media/mobile_gallery_music_album/index/value_id/:value_id/track_id/:track_id", {
        controller: 'MediaGalleryMusicAlbumController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_album/template",
        code: "media-gallery-music-page media-gallery-music-album"
    });

}).controller('MediaGalleryMusicAlbumController', function ($window, $rootScope, $scope, $routeParams, $location, Url, MediaMusicAlbum, MediaMusicTrack, MediaMusicTracksLoaderService, MediaMusicPlayerService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = $routeParams.value_id;
    MediaMusicAlbum.value_id = $routeParams.value_id;
    MediaMusicTrack.value_id = $routeParams.value_id;

    $scope.loadContent = function () {

        var param = {};
        if($routeParams.album_id) {
            param.album_id = $routeParams.album_id;
        } else {
            param.track_id = $routeParams.track_id;
        }
        MediaMusicAlbum.find(param).success(function (data) {
            $scope.album = data.album;
            
            MediaMusicTrack.findByAlbum(param).success(function (data) {

                $scope.album.tracks = data.tracks;

            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.play = function (trackIndex) {

        if(Application.handle_audio_player) {

            $window.audio_player_data = JSON.stringify(
                {
                    tracks: $scope.album.tracks,
                    albums: [{
                        id: $scope.album.id,
                        artworkUrl: $scope.album.artworkUrl,
                        name: $scope.album.name
                    }],
                    trackIndex: trackIndex
                }
            );
            Application.call("openAudioPlayer", $window.audio_player_data);

        } else {

            if ($scope.is_loading) return;

            MediaMusicPlayerService.init(document);

            var tracksLoader = MediaMusicTracksLoaderService._buildTracksLoaderForSingleAlbum($scope.album, $scope.album.tracks);

            MediaMusicPlayerService.playTracks(tracksLoader, trackIndex);

        }

    };

    $scope.loadContent();

});