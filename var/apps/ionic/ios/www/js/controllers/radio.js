App.config(function ($stateProvider) {

    $stateProvider.state('radio', {
        url: BASE_PATH + "/radio/mobile_radio/index/value_id/:value_id",
        controller: 'RadioController',
        templateUrl: 'templates/html/l1/loading.html'
    });

}).controller('RadioController', function ($scope, $stateParams, Application, MusicTracksLoader, MediaPlayer, Radio) {

    Radio.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        Radio.find().success(function (data) {
            var tracks_loader = MusicTracksLoader.loadSingleTrack({
                name: data.radio.title,
                artistName: "",
                streamUrl: data.radio.url,
                albumCover: data.radio.background,
                albumName: ""
            });

            MediaPlayer.init(tracks_loader, true, 0);
        });

    };

    $scope.loadContent();

});