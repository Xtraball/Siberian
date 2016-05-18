"use strict";

App.directive('playerLink', function ($location, $routeParams, Url, MediaMusicPlayerService) {
    return {
        restrict: 'A',
        templateUrl: Url.get('media/mobile_gallery_music_playerlink/template'),
        link: function ($scope, element, attrs) {
            $scope.$watch(function () {
                return MediaMusicPlayerService.isPlayerLinkVisible()
            }, function (visible) {
                $scope.visible = visible;
            });

            $scope.openPlayer = function () {

                MediaMusicPlayerService.openPlayer();
            };
        }
    };
});