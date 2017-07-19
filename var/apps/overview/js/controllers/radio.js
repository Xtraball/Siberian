/*global
 App, BASE_PATH
 */

angular.module("starter").controller("RadioController", function ($scope, $stateParams, MusicTracksLoader, MediaPlayer,
                                                                  Radio) {

    Radio.setValueId($stateParams.value_id);

    $scope.loadContent = function () {

        Radio.find()
            .then(function (data) {
                var tracks_loader = MusicTracksLoader.loadSingleTrack({
                    name        : data.radio.title,
                    artistName  : "",
                    streamUrl   : data.radio.url,
                    albumCover  : data.radio.background,
                    albumName   : ""
                });

                MediaPlayer.init(tracks_loader, true, 0);
            });

    };

    $scope.loadContent();

});