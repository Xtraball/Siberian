App.config(function ($stateProvider) {

    $stateProvider.state('media-player', {
        url: BASE_PATH + "/media/mobile_gallery_music_player/index/value_id/:value_id",
        controller: 'MediaPlayerController',
        templateUrl: "templates/media/music/l1/player/view.html"
    });

}).controller('MediaPlayerController', function($cordovaSocialSharing, $ionicHistory, $ionicModal, $location, $rootScope, $scope, $state, $stateParams, $timeout, $translate, $window, Application, HomepageLayout, MediaPlayer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if (args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_webview = !ionic.Platform.isIOS() && !ionic.Platform.isAndroid() && !ionic.Platform.isWindowsPhone();

    $ionicModal.fromTemplateUrl('templates/media/music/l1/player/playlist.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function (modal) {
        $scope.mediaplayer_playlist_modal = modal;
    });

    $scope.loadContent = function () {
        if (!MediaPlayer.media) {
            MediaPlayer.loading();
        }
    };

    $scope.minimize = function () {
        $scope.goBack();
        MediaPlayer.is_initialized = false;

        MediaPlayer.is_minimized = true;
        console.log("Minimizing");
        $rootScope.$broadcast("mediaPlayer.mini.show");
    };

    $scope.destroy = function () {
        $scope.goBack();
        MediaPlayer.destroy();
    };

    $scope.goBack = function() {

        if(MediaPlayer.is_radio && MediaPlayer.is_initialized) {
        	
        	/** l1_fixed && l9 needs another behavior */
        	HomepageLayout.getFeatures().then(function (features) {
                $scope.features = features;

                if (!Application.is_customizing_colors && HomepageLayout.properties.options.autoSelectFirst && ($scope.features && $scope.features.first_option !== false)) {
                    var feat_index = 0;
                    for(var fi = 0; fi < $scope.features.options.length; fi++) {
                        var feat = $scope.features.options[fi];
                        /** Don't load unwanted features on first page. */
                        if((feat.code !== "code_scan") && (feat.code !== "radio") && (feat.code !== "padlock")) {
                            feat_index = fi;
                            break;
                        }
                    }

                    if($scope.features.options[feat_index].path != $location.path()) {
                        $ionicHistory.nextViewOptions({
                            historyRoot: true,
                            disableAnimate: false
                        });

                        $location.path($scope.features.options[feat_index].path).replace();
                    }

                }
                else{
                    $ionicHistory.goBack(-2);
                }
        	});

        } else {
        	$ionicHistory.goBack(-1);
        }

    };

    // Playlist modal
    $scope.openPlaylist = function () {
        $scope.mediaplayer_playlist_modal.show();
    };

    $scope.closePlaylist = function () {
        $scope.mediaplayer_playlist_modal.hide();
    };

    $scope.selectTrack = function (index) {
        $scope.closePlaylist();

        $timeout(function () {
            MediaPlayer.loading();
            MediaPlayer.current_index = index;

            MediaPlayer.pre_start();
            MediaPlayer.start();
        }, 500);
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

    $scope.purchase = function () {

        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        if (MediaPlayer.current_track.purchaseUrl) {
            $window.open(MediaPlayer.current_track.purchaseUrl, $rootScope.getTargetForLink(), "location=no");
        }
    };

    $scope.share = function () {

        // Fix for $cordovaSocialSharing issue that opens dialog twice
        if($scope.is_sharing) return;

        $scope.is_sharing = true;

        var app_name = Application.app_name;
        var message = "";
        var link = "";
        var subject = "";
        var file = MediaPlayer.current_track.albumCover ? MediaPlayer.current_track.albumCover : "";

        if (MediaPlayer.is_radio) {
            message = $translate.instant("I'm listening to $1 on $2 app.").replace("$1", MediaPlayer.current_track.name).replace("$2", app_name);
        } else {
            message = $translate.instant("I'm listening to $1 from $2 on $3 app.").replace("$1", MediaPlayer.current_track.name).replace("$2", MediaPlayer.current_track.artistName).replace("$3", app_name);
        }

        $cordovaSocialSharing
            .share(message, subject, file, link) // Share via native share sheet
            .then(function (result) {
                console.log("success");
                $scope.is_sharing = false;
            }, function (err) {
                console.log(err);
                $scope.is_sharing = false;
            });
    };

    $scope.loadContent();

});