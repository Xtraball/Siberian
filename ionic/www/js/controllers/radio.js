/**
 * Radio controller
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
    .module('starter')
    .controller('RadioController', function ($scope, $stateParams, MusicTracksLoader, MediaPlayer, Radio) {
        Radio.setValueId($stateParams.value_id);

        $scope.loadContent = function () {
            Radio
                .find()
                .then(function (data) {
                    var tracksLoader = MusicTracksLoader.loadSingleTrack({
                        name: data.radio.title,
                        artistName: '',
                        streamUrl: data.radio.url,
                        albumCover: data.radio.background,
                        albumName: ''
                    });

                    MediaPlayer.init(tracksLoader, true, 0);
                });
        };

        $scope.loadContent();
    });
