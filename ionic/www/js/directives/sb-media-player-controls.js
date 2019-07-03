angular.module('starter').directive('sbMediaPlayerControls', function () {
    return {
        restrict: 'A',
        controller: function ($scope, $state, $timeout, $filter, MediaPlayer) {
            angular.extend($scope, {
                player: MediaPlayer
            });

            MediaPlayer.createModal($scope);

            $scope.openPlayer = function () {
                MediaPlayer.openPlayer();
            };

            $scope.duration = function () {
                if ($scope.player && $scope.player.media && $scope.player.media.duration) {
                    return $filter('seconds_to_minutes')($scope.player.media.duration);
                }
                return '0:00';
            };

            $scope.playPause = function () {
                MediaPlayer.playPause();
            };

            $scope.prev = function () {
                if (!MediaPlayer.is_minimized) {
                    MediaPlayer.loading();
                }
                MediaPlayer.prev();
            };

            $scope.next = function () {
                if (!MediaPlayer.is_minimized) {
                    MediaPlayer.loading();
                }
                MediaPlayer.next();
            };

            $scope.willSeek = function () {
                MediaPlayer.willSeek();
            };

            $scope.seekTo = function (position) {
                MediaPlayer.seekTo(position);
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

            // Playlist modal
            $scope.openPlaylist = function () {
                MediaPlayer.openPlaylist();
            };

            $scope.goBackMedia = function () {
                MediaPlayer.goBack(MediaPlayer.is_radio, true);
            };

            $scope.closePlaylist = function () {
                MediaPlayer.closePlaylist();
            };

            $scope.destroy = function (origin) {
                MediaPlayer.destroy(origin);
            };

            $scope.selectTrack = function (index) {
                MediaPlayer.closePlaylist();

                $timeout(function () {
                    MediaPlayer.loading();
                    MediaPlayer.current_index = index;

                    MediaPlayer.pre_start();
                    MediaPlayer.start();
                }, 500);
            };
        }
    };
});

angular.module('starter')
    .directive('miniElapsed', function ($interval, $filter, $timeout) {
        return {
            restrict: 'E',
            replace: true,
            template: '<span class="mini-elapsed">{{ elapsedTime }}</span>',
            scope: {
                seconds: '=elapsedTime'
            },
            link: function (scope, element) {
                var refreshTime = function () {
                    $timeout(function () {
                        scope.elapsedTime = $filter('seconds_to_minutes')(scope.seconds);
                    }, 0);
                };

                scope.elapsedTime = $filter('seconds_to_minutes')(scope.seconds);

                var stopTime = $interval(refreshTime, 1000);
                element.on('$destroy', function () {
                    $interval.cancel(stopTime);
                });
            }
        };
    });

angular.module('starter')
    .directive('sbMediaMiniPlayer', function () {
        return {
            restrict: 'E',
            templateUrl: 'templates/media/music/l1/player/mini.html'
        };
    });
