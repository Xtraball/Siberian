/**
 * MediaPlayer
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
    .module('starter')
    .service('MediaPlayer', function ($interval, $rootScope, $state, $log, $location, $ionicHistory,
                                      $stateParams, $timeout, $translate, $window, Application,
                                      HomepageLayout, Dialog, Modal, SB, $q) {
        var service = {
            media: null,
            isInitialized: false,
            isMinimized: true,
            isPlaying: false,
            isRadio: false,
            isShuffling: false,
            repeatType: null,
            shuffleTracks: [],
            tracks: [],
            currentIndex: 0,
            currentTrack: null,
            currentTab: 'cover',
            isBuffering: false,
            calledReset: false,
            isPrev: false,
            isNext: false,
            isSelecting: false,
            duration: 0,
            elapsedTime: 0,
            playerModal: null,
            playerModalIsOpen: false,
            value_id: null,
            useMusicControls: (SB.DEVICE.TYPE_BROWSER !== DEVICE_TYPE)
        };

        service.decodeCallback = function (result) {
            try {
                var localResult = JSON.parse(result);
                return localResult;
            } catch (e) {
                if (e.message.indexOf('Unexpected token') !== -1) {
                    return result;
                }
            }
            return result;
        };

        // Empty callback
        service.blankListener = function (event) {};

        service.musicControlsEventsHandler = function (event) {
            var response = service.decodeCallback(event);

            switch (response.message) {
                case 'music-controls-next':
                    // Do something
                    if (!service.isRadio) {
                        service.next();
                    }
                    break;
                case 'music-controls-previous':
                    // Do something
                    if (!service.isRadio) {
                        service.prev();
                    }
                    break;
                case 'music-controls-pause':
                case 'music-controls-play':
                case 'music-controls-media-button': // Headset events (Android only)
                case 'music-controls-toggle-play-pause': // External controls (iOS only)
                    service.playPause();
                    break;
                case 'music-controls-destroy':
                    service.reset();
                    break;

                case 'music-controls-seek-to':
                    const seekToInSeconds = response.position;
                    MusicControls.updateElapsed({
                        elapsed: seekToInSeconds,
                        isPlaying: true
                    });
                    break;

                case 'music-controls-headset-unplugged':
                    // Do something
                    break;
                case 'music-controls-headset-plugged':
                    // Do something
                    break;
            }
        };

        // Init media player
        service.init = function (tracksLoader, isRadio, trackIndex) {
            // Reset service when changing media feature!
            if ((service.value_id !== $stateParams.value_id) ||
                (service.media && (service.currentTrack.streamUrl !== tracksLoader.tracks[trackIndex].streamUrl))) {
                service.reset();
            }
            service._initCallback(tracksLoader, isRadio, trackIndex);
        };

        service._initCallback = function (tracksLoader, isRadio, trackIndex) {
            if (!service.media) {
                service.value_id = $stateParams.value_id;
                service.isRadio = isRadio;
                service.currentIndex = trackIndex;

                if (tracksLoader) {
                    service.tracks = tracksLoader.tracks;
                }
            }

            service.openPlayer();
        };

        service.play = function () {
            if (service.media) {
                service.isPlaying = true;
                service.media.play();
                service.updateSeekBar();
                service.updateMusicControls();
            }
        };

        service.pause = function () {
            if (service.media) {
                service.isPlaying = false;
                service.cancelSeekBar();
                service.media.pause();
                MusicControls.updateIsPlaying(service.isPlaying);
            }
        };

        service.stop = function () {
            if (service.media) {
                service.isPlaying = false;
                service.cancelSeekBar();
                service.media.stop();
                MusicControls.updateIsPlaying(service.isPlaying);
            }
        };

        service.preStart = function () {
            // Disabling battery optimizations, if required!
            try {
                if (DISABLE_BATTERY_OPTIMIZATION === true) {
                    MusicControls.disableBatteryOptimization();
                }
            } catch (e) {}

            service.pause();
            service.duration = 0;
            service.elapsedTime = 0;
            service.isMediaLoaded = false;
            service.isMediaStopped = false;
        };

        service.start = function () {
            service.currentTrack = service.tracks[service.currentIndex];

            if ((service.currentTrack.streamUrl.indexOf('http://') === -1) &&
                (service.currentTrack.streamUrl.indexOf('https://') === -1)) {
                Dialog.alert('Error', 'No current media to load.', 'OK', -1);
                return;
            }

            // Setting the albumCover image
            if (service.currentTrack.albumCover) {
                service.currentTrack.albumCover = service.currentTrack.albumCover
                    .replace('100x100bb', $window.innerWidth + 'x' + $window.innerWidth + 'bb');
            }

            // Clear the media on prev/next
            if (service.media) {
                service.stop();
                service.media.release();
            }
            service.media = new MediaNative(
                {
                    src: service.currentTrack.streamUrl,
                    isStream: service.isRadio ? 1 : 0
                },
                function (success) {},
                function (error) {
                    var response = service.decodeCallback(error);

                    try {
                        switch (parseInt(response, 10)) {
                            case MediaError.MEDIA_ERR_NONE_ACTIVE:
                                    // Ignore
                                break;
                            case MediaError.MEDIA_ERR_ABORTED:
                                Dialog.alert('Error', 'Media playing was aborted.', 'OK', -1);
                                break;
                            case MediaError.MEDIA_ERR_NETWORK:
                                Dialog.alert('Error', 'A network error occurred while loading the media.', 'OK', -1);
                                break;
                            case MediaError.MEDIA_ERR_DECODE:
                                Dialog.alert('Error', 'Unable to decode this media type.', 'OK', -1);
                                break;
                            case MediaError.MEDIA_ERR_NONE_SUPPORTED:
                                Dialog.alert('Error', 'This media type is not supported.', 'OK', -1);
                                break;
                        }
                    } catch (e) {
                        // Nope!
                    }
                },
                function (change) {
                    var response = service.decodeCallback(change);
                    // something changed, update controls & infos
                    if (service.media !== null) {
                        service.media.getDuration(function () {
                            service.duration = service.media._duration;
                        }, function () {});
                    }


                    // Play next if possible!
                    if (MediaNative.MEDIA_STOPPED === parseInt(response, 10)) {
                        if (!service.calledReset &&
                            !service.isPrev &&
                            !service.isNext &&
                            !service.isSelecting) {
                            // Reset was not called, it's a "track end stop", so we call next
                            service.next();
                        }
                        // Reset locks
                        service.calledReset = false;
                        service.isPrev = false;
                        service.isNext = false;
                        service.isSelecting = false;
                    }
                });

            service.play();
        };

        // Reset is promised based, as we have to wait on few events!
        service.reset = function () {
            service.calledReset = true;
            // First, we clear the seekbar/buffering updates!
            $interval.cancel(service.seekbarTimer);

            // Clear the subscriber
            MusicControls.subscribe(service.blankListener);

            // Release before destroy music controls
            service._releaseMediaPlayer();
            MusicControls.destroy();
        };

        service._releaseMediaPlayer = function () {
            if (service.media) {
                service.pause();
                service.stop();
                service.media.release();
            }
            service.media = null;
            service.seekbarTimer = null;
            service.isShuffling = false;
            service.isInitialized = false;
            $rootScope.$broadcast(SB.EVENTS.MEDIA_PLAYER.HIDE);

            // Clear player modal!
            if (service.playerModal !== null) {
                service.playerModal.remove();
                service.playerModal = null;
                service.playerModalIsOpen = false;
            }

            service.repeatType = null;
            service.currentIndex = 0;
            service.currentTrack = null;
            service.shuffleTracks = [];
        };

        service.openPlayer = function () {
            if (service.isInitialized ||
                service.playerModal !== null) {
                service.openPlayerModal('cover');
                return;
            }
            Modal
                .fromTemplateUrl('templates/media/music/l1/player/modal/player.html', {
                    scope: angular.extend($rootScope.$new(true), {
                        close: service.closePlayerModal
                    })
                })
                .then(function (modal) {
                    service.isInitialized = true;
                    service.playerModal = modal;

                    if (!service.media) {
                        $timeout(function () {
                            service.preStart();
                            service.start();
                        }, 1000);
                    }

                    service.openPlayerModal('cover');
                });
        };

        service.openPlayerModal = function (tab) {
            service.currentTab = (tab === undefined) ? 'cover' : tab;

            // Radio only has cover for now!
            if (service.isRadio) {
                service.currentTab = 'cover';
            }

            if (service.playerModal &&
                service.playerModal.isShown()) {
                return;
            }
            if (service.playerModal !== null) {
                service.playerModal.show();
                service.playerModalIsOpen = true;
            }
        };

        service.closePlayerModal = function () {
            if (service.playerModal &&
                !service.playerModal.isShown()) {
                return;
            }
            if (service.playerModal !== null) {
                service.playerModal.hide();
                service.playerModalIsOpen = false;
            }
        };

        service.playPause = function () {
            if (service.isPlaying) {
                service.pause();
            } else {
                service.play();
            }
        };

        service.selectTrack = function (index) {
            service.currentTab = 'cover';

            service.isSelecting = true;
            service.currentIndex = index;

            service.preStart();
            service.start();
        };

        service.prev = function () {
            if (service.isRadio) {
                return;
            }

            // Prevent change end to call next!
            service.isPrev = true;

            if (service.repeatType === 'one') {
                service.seekTo(0);
            } else if (service.isShuffling) {
                if (service.shuffleTracks.length >= service.tracks.length && service.repeatType === 'all') {
                    service.shuffleTracks = [];
                }

                service._randomSong();
            } else if ((service.repeatType === 'all') && (service.currentIndex === 0)) {
                service.currentIndex = service.tracks.length - 1;
            } else if (service.currentIndex > 0) {
                service.currentIndex = service.currentIndex - 1;
            }

            service.preStart();
            service.start();
        };

        service.next = function () {
            if (service.isRadio) {
                return;
            }

            // Prevent change end to call next!
            service.isNext = true;

            if (service.repeatType === 'one') {
                service.seekTo(0);
            } else {
                if (service.isShuffling) {
                    if ((service.shuffleTracks.length >= service.tracks.length) && (service.repeatType === 'all')) {
                        service.shuffleTracks = [];
                    }

                    service._randomSong();
                } else if ((service.repeatType === 'all') && (service.currentIndex >= (service.tracks.length - 1))) {
                    service.currentIndex = 0;
                } else if (service.currentIndex < (service.tracks.length - 1)) {
                    service.currentIndex = service.currentIndex + 1;
                }

                service.preStart();
                service.start();
            }
        };

        service._randomSong = function () {
            var random_index = Math.floor(Math.random() * service.tracks.length);

            while ((service.shuffleTracks.indexOf(random_index) !== -1) ||
            (random_index === service.currentIndex)) {
                if (service.shuffleTracks.indexOf(random_index) !== -1) {
                    random_index = Math.floor(Math.random() * service.tracks.length);
                } else {
                    random_index = random_index + 1;
                }
            }

            if (service.shuffleTracks.length >= service.tracks.length) {
                random_index = 0;
            }

            service.shuffleTracks.push(random_index);
            service.currentIndex = random_index;

            service.updateMusicControls();
        };

        service.backward = function () {
            var localSeekto = (service.elapsedTime - 10);
            if (localSeekto < 0) {
                service.prev();
            } else {
                service.elapsedTime = localSeekto;
            }
            service.seekTo(service.elapsedTime);
        };

        service.forward = function () {
            var localSeekto = (service.elapsedTime + 10);
            if (localSeekto > service.media.duration) {
                service.next();
            } else {
                service.elapsedTime = localSeekto;
            }
            service.seekTo(service.elapsedTime);
        };

        service.seekTo = function (position) {
            if (position === 0) {
                service.media.pause();
                service.isPlaying = false;
            }
            service.media.seekTo(position * 1000);
            if (!service.isPlaying) {
                service.playPause();
            }
        };

        service.repeat = function () {
            switch (service.repeatType) {
                case null:
                    service.repeatType = 'all';
                    break;

                case 'all':
                    service.repeatType = 'one';
                    break;

                case 'one':
                    service.repeatType = null;
                    break;
            }
        };

        service.shuffle = function () {
            service.shuffleTracks = [];
            service.isShuffling = !service.isShuffling;
        };

        service.updateMusicControls = function () {
            var hasPrev, hasNext = !service.isRadio;

            hasPrev = service.currentIndex !== 0;
            hasNext = service.currentIndex !== (service.tracks.length - 1);

            service.media.getCurrentPosition(function () {}, function () {});

            var mcDictionnary = {
                track: service.currentTrack.name,
                artist: service.currentTrack.artistName,
                cover: service.currentTrack.albumCover,
                isPlaying: true,
                dismissable: true,

                hasPrev: hasPrev,
                hasNext: hasNext,
                hasClose: true,

                // iOS only, optional
                album: service.currentTrack.albumName,
                duration: (service && service.media && service.media._duration) ?
                    service.media._duration * 1 : 0,
                elapsed: (service && service.media && service.media._position) ?
                    service.media._position * 1 : 0,

                // Android only, optional
                ticker: $translate.instant('Now playing ') + service.currentTrack.name
            };

            MusicControls.subscribe(service.musicControlsEventsHandler);
            MusicControls.listen();
            $timeout(function () {
                MusicControls.create(mcDictionnary,
                    function () {
                        MusicControls.updateIsPlaying(service.isPlaying);
                    }, function () {
                    });
            }, 20);
        };

        service.cancelSeekBar = function () {
            $interval.cancel(service.seekbarTimer);
        };

        service.updateSeekBar = function () {
            service.lastTime = -0.001;
            // First cancel to be sure!
            service.cancelSeekBar();
            service.seekbarTimer = $interval(function () {
                try {
                    service.media.getCurrentPosition(
                        function () {
                            if (service.media) {
                                service.elapsedTime = service.media._position;

                                // Buffer handling
                                if (service.media._position === -0.001 ||
                                    (service.lastTime === service.media._position)) {
                                    service.isBuffering = true;
                                } else {
                                    service.lastTime = service.media._position;
                                    service.isBuffering = false;
                                }
                            }
                        }, function () {});
                } catch (e) {
                    service.cancelSeekBar();
                }
            }, 500);
        };

        return service;
    });
