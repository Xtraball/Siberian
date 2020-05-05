/**
 * MediaPlayerController
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
    .module('starter')
    .controller('MediaPlayerController', function ($rootScope, $scope, MediaPlayer, SB, SocialSharing, LinkService) {

        $scope.isWebview = !$rootScope.isNativeApp;

        $scope.loadContent = function () {
            if (!MediaPlayer.media) {
                MediaPlayer.loading();
            }
        };

        $scope.backButton = function () {
            MediaPlayer.goBack(MediaPlayer.isRadio, true);
        };

        // When leaving the media (back button, or another state)
        //$scope.$on('$destroy', function () {
        //    if (MediaPlayer.isInitialized) {
        //        MediaPlayer.isMinimized = true;
        //        $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.SHOW, {
        //            isRadio: MediaPlayer.isRadio
        //        });
        //    }
        //});

        $scope.purchase = function () {
            if ($rootScope.isNotAvailableOffline()) {
                return;
            }

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

        $scope.loadContent();
    });
