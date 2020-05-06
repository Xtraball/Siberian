/**
 * sbMediaPlayerControls
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
    .module('starter')
    .directive('sbMediaPlayerControls', function () {
    return {
        restrict: 'A',
        controller: function ($scope, $state, $timeout, $filter, MediaPlayer) {
            angular.extend($scope, {
                player: MediaPlayer
            });

            $scope.isNativeApp = isNativeApp;

            $scope.openPlayerModal = function (tab) {
                // If the tab is not provided, button works as a toggler!
                if (tab === undefined) {
                    tab = $scope.player.currentTab === 'cover' ? 'playlist' : 'cover';
                }
                MediaPlayer.openPlayerModal(tab);
            };

            $scope.closePlayerModal = function () {
                MediaPlayer.closePlayerModal();
            };

            $scope.duration = function () {
                if ($scope.player &&
                    $scope.player.media &&
                    $scope.player.media._duration) {
                    return $filter('seconds_to_minutes')(Math.floor($scope.player.media._duration));
                }
                return '0:00';
            };

            $scope.playPause = function () {
                MediaPlayer.playPause();
            };

            $scope.prev = function () {
                MediaPlayer.prev();
            };

            $scope.next = function () {
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

            $scope.destroy = function () {
                MediaPlayer.destroy();
            };

            $scope.selectTrack = function (index) {
                MediaPlayer.currentTab = 'cover';

                $timeout(function () {
                    MediaPlayer.loading();
                    MediaPlayer.currentIndex = index;

                    MediaPlayer.preStart();
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
