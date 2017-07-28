/* global
 App, angular, BASE_PATH
 */

angular.module('starter').controller('MediaPlayerController', function ($cordovaSocialSharing, $ionicHistory, Modal,
                                                                       $location, $rootScope, $scope, $state,
                                                                       $stateParams, $timeout, $translate, $window,
                                                                       Application, HomepageLayout, MediaPlayer,
                                                                       SB, SocialSharing, LinkService) {
    $scope.is_webview = !$rootScope.isNativeApp;

    Modal
        .fromTemplateUrl('templates/media/music/l1/player/playlist.html', {
            scope: $scope
        })
        .then(function (modal) {
            $scope.mediaplayer_playlist_modal = modal;
        });

    $scope.loadContent = function () {
        if (!MediaPlayer.media) {
            MediaPlayer.loading();
        }
    };

    $scope.minimize = function () {
        $scope.goBack();
        MediaPlayer.is_initialized = false;

        MediaPlayer.is_minimized = true;
        $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.SHOW);
    };

    $scope.destroy = function () {
        $scope.goBack();
        MediaPlayer.destroy();
    };

    $scope.goBack = function () {
        if (MediaPlayer.is_radio && MediaPlayer.is_initialized) {
            // l1_fixed && l9 needs another behavior!
            HomepageLayout.getFeatures()
                .then(function (features) {
                    $scope.features = features;

                    if (!Application.is_customizing_colors && HomepageLayout.properties.options.autoSelectFirst &&
                        ($scope.features && $scope.features.first_option !== false)) {
                        var featIndex = 0;
                        for (var fi = 0; fi < $scope.features.options.length; fi = fi + 1) {
                            var feat = $scope.features.options[fi];
                            // Don't load unwanted features on first page!
                            if ((feat.code !== 'code_scan') && (feat.code !== 'radio') && (feat.code !== 'padlock')) {
                                featIndex = fi;
                                break;
                            }
                        }

                        if ($scope.features.options[featIndex].path != $location.path()) {
                            $ionicHistory.nextViewOptions({
                                historyRoot: true,
                                disableAnimate: false
                            });

                            $location.path($scope.features.options[featIndex].path).replace();
                        }
                    } else {
                        $ionicHistory.goBack(-2);
                    }
                });
        } else {
            $ionicHistory.goBack(-1);
        }
    };

    // Playlist modal
    $scope.openPlaylist = function () {
        $scope.mediaplayer_playlist_modal.show();
    };

    $scope.closePlaylist = function () {
        $scope.mediaplayer_playlist_modal.hide();
    };

    $scope.selectTrack = function (index) {
        $scope.closePlaylist();

        $timeout(function () {
            MediaPlayer.loading();
            MediaPlayer.current_index = index;

            MediaPlayer.pre_start();
            MediaPlayer.start();
        }, 500);
    };

    $scope.backward = function () {
        MediaPlayer.backward();
    };

    $scope.forward = function () {
        MediaPlayer.forward();
    };

    $scope.repeat = function () {
        MediaPlayer.repeat();
    };

    $scope.shuffle = function () {
        MediaPlayer.shuffle();
    };

    $scope.purchase = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if (MediaPlayer.current_track.purchaseUrl) {
            LinkService.openLink(MediaPlayer.current_track.purchaseUrl);
        }
    };

    $scope.share = function () {
        var content = MediaPlayer.current_track.name;
        if (!MediaPlayer.is_radio) {
            content = MediaPlayer.current_track.name + ' from ' + MediaPlayer.current_track.artistName;
        }
        var file = MediaPlayer.current_track.albumCover ? MediaPlayer.current_track.albumCover : undefined;

        SocialSharing.share(content, undefined, undefined, undefined, file);
    };

    $scope.loadContent();
});
