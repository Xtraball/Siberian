/* global
    App, angular, ionic, MusicControls, DEVICE_TYPE, Audio
 */

/**
 * MediaPlayer
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('MediaPlayer', function ($interval, $rootScope, $state, $log, $stateParams, $timeout,
                                     $translate, $window, Dialog, Loader, SB) {
    var service = {
        media: null,

        is_initialized: false,
        is_minimized: false,
        is_playing: false,
        is_radio: false,
        is_shuffling: false,
        is_stream: false,

        repeat_type: null,

        shuffle_tracks: [],
        tracks: [],

        current_index: 0,
        current_track: null,

        duration: 0,
        elapsed_time: 0,

        value_id: null,

        use_music_controls: (SB.DEVICE.TYPE_BROWSER !== DEVICE_TYPE)
    };

    service.loading = function () {
        var message = $translate.instant('Loading');
        if (service.is_radio) {
            message = $translate.instant('Buffering');
        }

        Loader.show(message);
    };

    var music_controls_events = function (event) {
        switch (event) {
            case 'music-controls-next':
                    // Do something
                    if (!service.is_radio) {
                        service.next();
                    }
                break;
            case 'music-controls-previous':
                    // Do something
                    if (!service.is_radio) {
                        service.prev();
                    }
                break;
            case 'music-controls-pause':
            case 'music-controls-play':
            // External controls (iOS only)
            case 'music-controls-toggle-play-pause' :
                    service.playPause();
                break;
            case 'music-controls-destroy':
                    service.destroy();
                break;

            // Headset events (Android only)
            // All media button events are listed below
            case 'music-controls-media-button' :
                    // Do something
                break;
            case 'music-controls-headset-unplugged':
                    // Do something
                break;
            case 'music-controls-headset-plugged':
                    // Do something
                break;
        }
    };

    service.init = function (tracks_loader, is_radio, track_index) {
        // Destroy service when changing media feature!
        if (service.value_id !== $stateParams.value_id) {
            service.destroy();
        }

        if (service.media && (service.current_track.streamUrl !== tracks_loader.tracks[track_index].streamUrl)) {
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

        if (service.use_music_controls) {
            MusicControls.subscribe(music_controls_events);
            MusicControls.listen();
        }
    };

    service.play = function () {
        service.media.play();
        service.is_playing = true;
    };

    service.pre_start = function () {
        if (service.media) {
            service.media.pause();
        }

        service.is_playing = false;
        service.duration = 0;
        service.elapsed_time = 0;
        service.is_media_loaded = false;
        service.is_media_stopped = false;
    };

    service.start = function () {
        service.current_track = service.tracks[service.current_index];

        $log.info(service.current_track, service.tracks);

        if ((service.current_track.streamUrl.indexOf('http://') === -1) &&
            (service.current_track.streamUrl.indexOf('https://') === -1)) {
            Loader.hide();
            Dialog.alert('Error', 'No current stream to load.', 'OK', -1);
            return;
        }

        // Setting the albumCover image
        if (service.current_track.albumCover) {
            service.current_track.albumCover = service.current_track.albumCover.replace('100x100bb', $window.innerWidth + 'x' + $window.innerWidth + 'bb');
        }

        service.is_stream = service.is_radio;
        $log.debug(service.current_track);

        service.media = new Audio(service.current_track.streamUrl);
        service.media.onended = function() {
            service.next();
        };
        service.play();

        service.updateSeekBar();

        Loader.hide();

        service.updateMusicControls();
    };

    service.reset = function () {
        service.media = null;
        service.seekbarTimer = null;
        service.is_shuffling = false;
        service.is_initialized = false;

        service.is_minimized = false;
        $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.HIDE);

        service.repeat_type = null;
        service.current_index = 0;
        service.current_track = null;
        service.shuffle_tracks = [];

        if (service.use_music_controls) {
            MusicControls.destroy();
            MusicControls.subscribe(music_controls_events);
            MusicControls.listen();
        }
    };

    service.destroy = function () {

        $interval.cancel(service.seekbarTimer);
        if (service.media) {
            if (service.is_playing) {
                service.media.pause();
            }
        }

        service.reset();
    };

    service.openPlayer = function () {
        $state.go('media-player', {
            value_id: service.value_id
        });

        service.is_minimized = false;

        $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.HIDE);

        if (!service.media) {
            $timeout(function () {
                service.pre_start();
                service.start();
            }, 1000);
        }
    };

    service.playPause = function () {
        if (service.is_playing) {
            service.media.pause();

            $interval.cancel(service.seekbarTimer);

            if (service.use_music_controls) {
                MusicControls.updateIsPlaying(false);
            }
        } else {
            service.media.play();

            if (service.use_music_controls) {
                MusicControls.updateIsPlaying(true);
            }
        }

        service.is_playing = !service.is_playing;

        service.updateMusicControls();
    };

    service.prev = function () {
        if (service.repeat_type === 'one') {
            service.seekTo(0);
        } else if (service.is_shuffling) {
                if (service.shuffle_tracks.length >= service.tracks.length && service.repeat_type === 'all') {
                    service.shuffle_tracks = [];
                }

                service._randomSong();
            } else if ((service.repeat_type === 'all') && (service.current_index === 0)) {
                service.current_index = service.tracks.length - 1;
            } else if (service.current_index > 0) {
                service.current_index = service.current_index - 1;
            }

        service.pre_start();
        service.start();
    };

    service.next = function () {
        if (service.repeat_type === 'one') {
            service.seekTo(0);
        } else {
            if (service.is_shuffling) {
                if ((service.shuffle_tracks.length >= service.tracks.length) && (service.repeat_type === 'all')) {
                    service.shuffle_tracks = [];
                }

                service._randomSong();
            } else if ((service.repeat_type === 'all') && (service.current_index >= (service.tracks.length - 1))) {
                service.current_index = 0;
            } else if (service.current_index < (service.tracks.length - 1)) {
                service.current_index = service.current_index + 1;
            }

            service.pre_start();
            service.start();
        }
    };

    service._randomSong = function () {
        var random_index = Math.floor(Math.random() * service.tracks.length);

        while ((service.shuffle_tracks.indexOf(random_index) !== -1) || (random_index === service.current_index)) {
            if (service.shuffle_tracks.indexOf(random_index) !== -1) {
                random_index = Math.floor(Math.random() * service.tracks.length);
            } else {
                random_index = random_index + 1;
            }
        }

        if (service.shuffle_tracks.length >= service.tracks.length) {
            random_index = 0;
        }

        service.shuffle_tracks.push(random_index);
        service.current_index = random_index;

        service.updateMusicControls();
    };

    service.backward= function () {
        var tmp_seekto = (service.elapsed_time - 10);
        if (tmp_seekto < 0) {
            service.prev();
        } else {
            service.elapsed_time = tmp_seekto;
        }
        service.seekTo(service.elapsed_time);
    };

    service.forward = function () {
        var tmp_seekto = (service.elapsed_time + 10);
        if (tmp_seekto > service.media.duration) {
            service.next();
        } else {
            service.elapsed_time = tmp_seekto;
        }
        service.seekTo(service.elapsed_time);
    };

    service.willSeek = function () {
        if (service.is_playing) {
            service.media.pause();
            service.is_playing = false;
        }
    };

    service.seekTo = function (position) {
        if (position === 0) {
            service.media.pause();
            service.is_playing = false;
        }
        service.media.currentTime = position;
        if (!service.is_playing) {
            service.playPause();
        }
    };

    service.repeat = function () {
        switch (service.repeat_type) {
            case null:
                service.repeat_type = 'all';
                break;

            case 'all':
                service.repeat_type = 'one';
                break;

            case 'one':
                service.repeat_type = null;
                break;
        }
    };

    service.shuffle = function () {
        service.shuffle_tracks = [];
        service.is_shuffling = !service.is_shuffling;
    };

    service.updateMusicControls = function () {
        if (service.use_music_controls) {
            var hasPrev = true;
            var hasNext = true;
            if (service.is_radio) {
                hasPrev = false;
                hasNext = false;
            }

            if (service.current_index === 0) {
                hasPrev = false;
            }

            if (service.current_index === (service.tracks.length - 1)) {
                hasNext = false;
            }

            MusicControls.create({
                track: service.current_track.name,
                artist: service.current_track.artistName,
                cover: service.current_track.albumCover,
                isPlaying: true,
                dismissable: true,

                hasPrev: hasPrev,
                hasNext: hasNext,
                hasClose: true,

                // iOS only, optional
                album: service.current_track.albumName,
                duration: service.media.duration,
                elapsed: service.media.currentTime,

                // Android only, optional
                ticker: $translate.instant('Now playing ') + service.current_track.name
            }, function () {
                $log.debug('success');
            }, function () {
                $log.debug('error');
            });
        }
    };

    service.updateSeekBar = function () {
        service.seekbarTimer = $interval(function () {
            if (service.is_playing) {
                service.elapsed_time = service.media.currentTime;
            }

            if (!service.is_radio && service.is_media_stopped && service.is_media_loaded) {
                $interval.cancel(service.seekbarTimer);
                service.is_media_stopped = false;
                service.next();
            }
        }, 100);
    };

    return service;
});
