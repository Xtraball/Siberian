App.service('MediaPlayer', function ($interval, $ionicLoading, $location, $rootScope, $state, $stateParams, $timeout, $translate, $window, Application, Dialog) {

    this.media = null;

    this.is_initialized = false;
    this.is_minimized = false;
    this.is_playing = false;
    this.is_radio = false;
    this.is_shuffling = false;
    this.is_stream = false;

    this.repeat_type = null;

    this.shuffle_tracks = [];
    this.tracks = [];

    this.current_index = 0;
    this.current_track = null;

    this.duration = 0;
    this.elapsed_time = 0;

    this.value_id = null;

    var service = this;

    service.loading = function() {
        $ionicLoading.show({
            content: 'Loading',
            animation: 'fade-in',
            maxWidth: 200
        });
    };

    var _remoteEvent = function(event) {
        $timeout(function () {
            switch (event.remoteEvent.subtype) {
                case "pause":
                case "play":
                    service.playPause();
                    break;

                case "nextTrack":
                    if (!service.is_radio) service.next();
                    break;

                case "prevTrack":
                    if (!service.is_radio) service.prev();
                    break;
            }
        });
    };

    service.init = function(tracks_loader, is_radio, track_index) {
        if(service.media && service.current_track.streamUrl != tracks_loader.tracks[track_index].streamUrl) {
            service.destroy();
        }

        if (!service.media) {
            service.value_id = $stateParams.value_id;
            service.is_radio = is_radio;
            service.current_index = track_index;

            if (tracks_loader) {
                service.tracks = tracks_loader.tracks;
            }
        }

        service.is_initialized = true;
        service.openPlayer();

        window.addEventListener("remote-event", _remoteEvent);
    };

    service.pre_start = function() {
        if(service.media) {
            if(service.is_stream) {
                service.media.stop();
            } else {
                if(service.is_playing) service.media.pause();
                service.media.release();
            }
        }

        service.is_playing = false;
        service.duration = 0;
        service.elapsed_time = 0;
        service.is_media_loaded = false;
        service.is_media_stopped = false;
    };

    service.start = function() {
        service.current_track = service.tracks[service.current_index];

        if(service.current_track.streamUrl.indexOf("http://") == -1 && service.current_track.streamUrl.indexOf("https://") == -1) {
            Dialog.alert("", $translate.instant('No current stream to load.'), $translate.instant("OK"));
            $ionicLoading.hide();
            return;
        }

        // Setting the albumCover image
        if (service.current_track.albumCover) {
            service.current_track.albumCover = service.current_track.albumCover.replace("100x100bb", $window.innerWidth + "x" + $window.innerWidth + "bb")
        }

        if(service.is_radio && !Application.is_webview) {
            service.is_stream = true;
            service.media = new Stream(service.current_track.streamUrl, null, function (err) {
                $ionicLoading.hide();
                service.is_playing = false;

                Dialog.alert($translate.instant('Error'), $translate.instant('An error occurred while loading the radio.'), $translate.instant("OK"));
            });
            service.media.play();
            $timeout(function() {
                service.is_playing = true;
                $ionicLoading.hide();
            }, 1000);
        } else {
            service.is_stream = false;
            service.media = new Media(service.current_track.streamUrl, null, function (err) {
                $ionicLoading.hide();

                Dialog.alert($translate.instant('Error'), $translate.instant('An error occurred while loading the media.'), $translate.instant("OK"));
            }, function (status) {
                service.update(status);
            });

            if(service.media && service.is_radio && Application.is_webview) {
                $ionicLoading.hide();
            } else {
                service.playPause();
            }
        }
    };

    service.reset = function() {
        service.media = null;
        service.seekbarTimer = null;
        service.is_shuffling = false;
        service.is_initialized = false;

        service.is_minimized = false;
        $rootScope.$broadcast("mediaPlayer.mini.hide");

        service.repeat_type = null;
        service.current_index = 0;
        service.current_track = null;
        service.shuffle_tracks = [];

        window.removeEventListener("remote-event", _remoteEvent);
    };

    service.destroy = function() {
        if(service.media) {
            if (service.is_playing) {
                if (service.is_stream) {
                    service.media.stop();
                } else {
                    $interval.cancel(service.seekbarTimer);
                    service.media.pause();
                }
            }

            if (!service.is_stream) {
                service.media.release();
            }
        }

        service.reset();
    };

    service.openPlayer = function() {
        $state.go('media-player', { value_id: service.value_id });

        service.is_minimized = false;
        $rootScope.$broadcast("mediaPlayer.mini.hide");

        if(!service.media) {
            $timeout(function() {
                service.pre_start();
                service.start();
            }, 1000);
        }
    };

    service.playPause = function() {
        if(service.is_playing) {
            if(service.is_stream) {
                service.media.stop();
            } else {
                service.media.pause();
            }
        } else {
            if(ionic.Platform.isIOS()) {
                service.media.play({playAudioWhenScreenIsLocked: true});
                service.updateIosRemoteControls();
            } else {
                service.media.play();
            }
        }

        if(service.is_stream) {
            $timeout(function() {
                service.is_playing = !service.is_playing;
            });
        }

        if(ionic.Platform.isIOS()) {
            service.updateIosRemoteControls();
        }
    };

    service.prev = function() {
        if(service.repeat_type == "one") {
            service.seekTo(0);
        } else {

            if (service.is_shuffling) {

                if (service.shuffle_tracks.length >= service.tracks.length && service.repeat_type == "all") {
                    service.shuffle_tracks = [];
                }

                service._randomSong();

            } else if (service.repeat_type == 'all' && service.current_index == 0) {
                service.current_index = service.tracks.length - 1;
            } else if (service.current_index > 0) {
                service.current_index--;
            }

        }

        service.pre_start();
        service.start();
    };

    service.next = function() {
        if (service.repeat_type == "one") {
            service.seekTo(0);
        } else {

            if (service.is_shuffling) {

                if (service.shuffle_tracks.length >= service.tracks.length && service.repeat_type == "all") {
                    service.shuffle_tracks = [];
                }

                service._randomSong();

            } else if (service.repeat_type == 'all' && service.current_index >= (service.tracks.length - 1)) {
                service.current_index = 0;
            } else if (service.current_index < (service.tracks.length - 1)) {
                service.current_index++;
            }

            service.pre_start();
            service.start();
        }
    };

    service._randomSong = function() {
        var random_index = Math.floor(Math.random() * service.tracks.length);

        while (service.shuffle_tracks.indexOf(random_index) != -1 || random_index == service.current_index) {
            if(service.shuffle_tracks.indexOf(random_index) != -1) {
                random_index = Math.floor(Math.random() * service.tracks.length);
            } else {
                random_index++;
            }
        }

        if (service.shuffle_tracks.length >= service.tracks.length) {
            random_index = 0;
        }

        service.shuffle_tracks.push(random_index);
        service.current_index = random_index;
    };

    service.backward= function() {
        var tmp_seekto = (service.elapsed_time - 10);
        if(tmp_seekto < 0) {
            service.prev();
        } else {
            service.elapsed_time = tmp_seekto;
        }
        service.seekTo(service.elapsed_time);
    };

    service.forward = function() {
        var tmp_seekto = (service.elapsed_time + 10);
        if(tmp_seekto > service.duration) {
            service.next();
        } else {
            service.elapsed_time = tmp_seekto;
        }
        service.seekTo(service.elapsed_time);
    };

    service.willSeek = function() {
        if(service.is_playing) {
            service.media.pause();
            service.is_playing = false;
        }
    };

    service.seekTo = function(position) {
        service.media.seekTo(position * 1000);
        if(!service.is_playing) {
            service.playPause();
        }
    };

    service.repeat = function() {
        switch(service.repeat_type) {
            case null:
                service.repeat_type = "all";
                break;

            case 'all':
                service.repeat_type = "one";
                break;

            case 'one':
                service.repeat_type = null;
                break;
        }
    };

    service.shuffle = function() {
        service.shuffle_tracks = [];
        service.is_shuffling = !service.is_shuffling;
    };

    service.updateIosRemoteControls = function() {
        var params = [service.current_track.artistName, service.current_track.name, service.current_track.albumName, service.current_track.albumCover, service.duration, service.elapsed_time];

        $window.remoteControls.updateMetas(function(success){
            console.log("success update");
        }, function(fail){
            console.log(fail);
        }, params);
    };

    service.update = function(status) {
        if(status == Media.MEDIA_RUNNING) {
            // Hide seekbar if stream is a radio
            if(!service.is_radio) {
                service.updateSeekBar();
            }

            if (!service.is_media_loaded) {
                if(service.current_track.duration) {
                    service.duration = service.current_track.duration / 1000;
                } else {
                    service.duration = service.media.getDuration();
                }

                service.is_media_loaded = true;
            }

            $timeout(function(){
                service.is_media_stopped = false;
                service.is_playing = true;
                $ionicLoading.hide();
            }, 500);
        } else if(status == Media.MEDIA_PAUSED) {
            service.is_playing = false;
            $interval.cancel(service.seekbarTimer);
        } else if(status == Media.MEDIA_STOPPED) {
            service.is_media_stopped = true;
            service.is_playing = false;
        }

    };

    service.updateSeekBar = function() {
        service.seekbarTimer = $interval(function () {
            if(service.is_playing) {
                service.media.getCurrentPosition(
                    function (current_position) {
                        service.elapsed_time = current_position;
                    },
                    function (error) {
                        console.log(error);
                    }
                );
            }

            if (!service.is_radio && service.is_media_stopped && service.is_media_loaded) {
                $interval.cancel(service.seekbarTimer);
                service.is_media_stopped = false;
                service.next();
            }
        }, 100);
    };

});