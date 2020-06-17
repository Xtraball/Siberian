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
        controller: function ($scope, $state, $timeout, $filter, MediaPlayer, LinkService, SocialSharing) {
            angular.extend($scope, {
                player: MediaPlayer
            });

            $scope.purchase = function () {
                if (MediaPlayer.currentTrack.purchaseUrl) {
                    LinkService.openLink(MediaPlayer.currentTrack.purchaseUrl, {}, true);
                }
            };

            $scope.share = function () {
                var content = MediaPlayer.currentTrack.name;
                if (!MediaPlayer.isRadio) {
                    content = MediaPlayer.currentTrack.name + ' from ' + MediaPlayer.currentTrack.artistName;
                }
                var file = MediaPlayer.currentTrack.albumCover ? MediaPlayer.currentTrack.albumCover : undefined;

                SocialSharing.share(content, undefined, undefined, undefined, file);
            };

            $scope.duration = function () {
                if ($scope.player &&
                    $scope.player.media &&
                    $scope.player.media._duration &&
                    $scope.player.media._duration > 0) {
                    return $filter('seconds_to_human')(Math.floor($scope.player.media._duration));
                }
                return '0:00';
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
                        scope.elapsedTime = $filter('seconds_to_human')(scope.seconds);
                    }, 0);
                };

                scope.elapsedTime = $filter('seconds_to_human')(scope.seconds);

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
