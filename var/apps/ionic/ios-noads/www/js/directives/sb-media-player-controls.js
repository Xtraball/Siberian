App.directive('sbMediaPlayerControls', function () {
    return {
        restrict: 'A',
        controller: function($scope, $state, $timeout, MediaPlayer) {
            $scope.player = MediaPlayer;

            $scope.openPlayer = function() {
                MediaPlayer.openPlayer();
            };

            $scope.playPause = function() {
                MediaPlayer.playPause();
            };

            $scope.prev = function() {
                if(!MediaPlayer.is_minimized) {
                    MediaPlayer.loading();
                }
                MediaPlayer.prev();
            };

            $scope.next = function() {
                if(!MediaPlayer.is_minimized) {
                    MediaPlayer.loading();
                }
                MediaPlayer.next();
            };

            $scope.willSeek = function() {
                MediaPlayer.willSeek();
            };

            $scope.seekTo = function(position) {
                MediaPlayer.seekTo(position);
            };
        }
    };
});

App.directive('sbMediaMiniPlayer', function () {
    return {
        restrict: 'E',
        templateUrl: 'templates/media/music/l1/player/mini.html'
    };
});