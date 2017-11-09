App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/radio/mobile_radio/index/value_id/:value_id", {
        controller: 'RadioController',
        template: ''
    });

}).controller('RadioController', function ($window, $scope, $routeParams, $location, $timeout, Url, Radio, MediaMusicTracksLoaderService, MediaMusicPlayerService) {

    Radio.value_id = $routeParams.value_id;

    $scope.loadContent = function () {

        Radio.find().success(function (data) {

            if(Application.handle_audio_player) {

                $window.audio_player_data = JSON.stringify(
                    {
                        tracks: [{
                            name: data.radio.title,
                            streamUrl: data.radio.url
                        }],
                        isRadio: true
                    }
                );
                Application.call("openAudioPlayer", $window.audio_player_data);

                $timeout(function() {
                    $window.history.back();
                }, 2000);

            } else {

                MediaMusicPlayerService.init(document);

                var tracksLoader = MediaMusicTracksLoaderService.loadSingleTrack({
                    name: data.radio.title,
                    streamUrl: data.radio.url
                });

                MediaMusicPlayerService.playTracks(tracksLoader, 0, true, true);

            }

        });

    };

    $scope.loadContent();

});