"use strict";

App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/media/mobile_gallery_music_player/index/value_id/:value_id", {
        controller: 'MediaGalleryMusicPlayerController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_player/template",
        code: "media-gallery-music-player"
    });

}).controller('MediaGalleryMusicPlayerController', function ($scope, $rootScope, $routeParams, $location, $window, $timeout, Url, MediaMusicPlayerService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.is_loading_more_tracks = false;

    $scope.loadContent = function () {

            MediaMusicPlayerService.init(document);

            MediaMusicPlayerService.audio().addEventListener('timeupdate', function (evt) {
                $timeout(function () {
                    $scope.currentTime = MediaMusicPlayerService.audio().currentTime;
                    $scope.ellapsedTime = $scope.currentTime * 1000;
                    $scope.progress = Math.round(100 * $scope.currentTime / $scope.duration);
                });
            });

            MediaMusicPlayerService.audio().addEventListener('durationchange', function (evt) {
                $timeout(function () {
                    $scope.duration = MediaMusicPlayerService.audio().duration;
                });
            });

            $scope.is_loading = false;

    };

    $scope.player = MediaMusicPlayerService;

    $scope.updateVolumeTo = function (newVolume) {
        var volume = MediaMusicPlayerService.audio().volume;
        if (typeof (volume) !== 'undefined' && !isNaN(volume)) {
            MediaMusicPlayerService.audio().volume = newVolume;
        }
    };

    $scope.currentVolume = function () {
        if ($scope.player && $scope.player.audio() && $scope.player.audio().volume) {
            return $scope.player.audio().volume;
        } else {
            return 0;
        }
    };

    $scope.updateTrackPosition = function (newValue, oldValue) {
        var duration = MediaMusicPlayerService.audio().duration;
        if (typeof (duration) !== 'undefined' && !isNaN(duration)) {
            var newTime = Math.round(newValue * duration / 100);
            MediaMusicPlayerService.audio().currentTime = newTime;
            MediaMusicPlayerService.control.play();
        }
    }

    $scope.updateVolume = function ($event) {
        var rect = $event.currentTarget.getBoundingClientRect();
        var clickOffset = $event.clientX - rect.left;
        var total = rect.width;
        var volume = MediaMusicPlayerService.audio().volume;
        var newVolume = clickOffset / total;
        $scope.updateVolumeTo(newVolume);
    };

    $scope.playTrack = function ($index) {
        MediaMusicPlayerService.control.switchTo($index);
        MediaMusicPlayerService.control.play();
    };

    $scope.hide = function () {
        if ($window.history.length === 0) {
            // can't go back, so open playlists index page
            $location.path(Url.get("media/mobile_gallery_music_playlists/index", {
                value_id: $routeParams.value_id
            }));
        } else {
            // go back
            $window.history.back();
        }
    };

    $scope.currentLocation = window.location.href;

    var msgPartIamListening = angular.element(document.querySelector('#twitterMessagePart1')).val();
    var msgPartMusic = angular.element(document.querySelector('#twitterMessagePart2')).val();
    var msgPartFrom = angular.element(document.querySelector('#twitterMessagePart3')).val();
    var msgPartOn = angular.element(document.querySelector('#twitterMessagePart4')).val();

    $scope.twitterMessage = function () {
        var twitter_message = msgPartIamListening;

        var currentTrack = MediaMusicPlayerService.getCurrentTrack();

        if (currentTrack) {
            twitter_message += ' ' + currentTrack.name;
            if (currentTrack.artistName !== null) {
                twitter_message += ' ' + msgPartFrom + ' ' + currentTrack.artistName;
            }
        } else {
            twitter_message += ' ' + msgPartMusic;
        }
        twitter_message += ' ' + msgPartOn;
        twitter_message = encodeURIComponent(twitter_message);

        return twitter_message;
    };

    $scope.enable_load_onscroll = true;

    $scope.clearPlaylist = function () {
        MediaMusicPlayerService.clear();
        $scope.currentTime = null;
        $scope.duration = null;
        $scope.ellapsedTime = null;
        $scope.progress = 0;
    }

    $scope.loadMore = function () {

        $scope.is_loading_more_tracks = true;

        MediaMusicPlayerService.loadMore(50).then(function (results) {
            $scope.is_loading_more_tracks = false;
        });

    };

    $scope.close = function () {
        MediaMusicPlayerService.clear();
        $scope.hide();
    };

    $scope.loadContent();

});