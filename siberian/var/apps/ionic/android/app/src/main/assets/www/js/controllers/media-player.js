/* global
 App, angular, BASE_PATH
 */

angular.module('starter').controller('MediaPlayerController', function ($cordovaSocialSharing, $ionicHistory, Modal,
                                                                       $location, $rootScope, $scope, $state,
                                                                       $stateParams, $timeout, $translate, $window,
                                                                       Application, HomepageLayout, MediaPlayer,
                                                                       SB, SocialSharing, LinkService) {
    $scope.is_webview = !$rootScope.isNativeApp;

    $scope.loadContent = function () {
        if (!MediaPlayer.media) {
            MediaPlayer.loading();
        }
    };

    $scope.backButton = function () {
        MediaPlayer.goBack(MediaPlayer.is_radio, true);
    };

    // When leaving the media (back button, or another state
    $scope.$on('$destroy', function () {
        if (MediaPlayer.is_initialized) {
            MediaPlayer.is_minimized = true;
            $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.SHOW, {
                isRadio: MediaPlayer.is_radio
            });
        }
    });

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
