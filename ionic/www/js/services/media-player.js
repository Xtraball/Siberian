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
                                      HomepageLayout, Dialog, Modal, Loader, SB) {
    var service = {
        media: null,
        isInitialized: false,
        isMinimized: true,
        isPlaying: false,
        isRadio: false,
        isShuffling: false,
        isStream: false,
        repeatType: null,
        shuffleTracks: [],
        tracks: [],
        currentIndex: 0,
        currentTrack: null,
        currentTab: 'cover',
        duration: 0,
        elapsedTime: 0,
        playerModal: null,
        playerModalIsOpen: false,
        value_id: null,
        useMusicControls: (SB.DEVICE.TYPE_BROWSER !== DEVICE_TYPE)
    };

    service.loading = function () {
        var message = $translate.instant('Loading', 'media');
        if (service.isRadio) {
            message = $translate.instant('Buffering', 'media');
        }

        Loader.show(message);
    };

    var musicControlsEventsHandler = function (event) {
        var response = JSON.parse(event);

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
            // External controls (iOS only)
            case 'music-controls-toggle-play-pause' :
                service.playPause();
                break;
            case 'music-controls-destroy':
                service.destroy("player");
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

    service.init = function (tracksLoader, isRadio, trackIndex) {
        // Destroy service when changing media feature!
        if (service.value_id !== $stateParams.value_id) {
            service.destroy();
        }

        if (service.media && (service.currentTrack.streamUrl !== tracksLoader.tracks[trackIndex].streamUrl)) {
            service.destroy();
        }

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
        service.media.play();
        service.isPlaying = true;
    };

    service.preStart = function () {
        // Trying to disable battery optimizations.
        try {
            if (DISABLE_BATTERY_OPTIMIZATION === true) {
                MusicControls.disableBatteryOptimization();
            }
        } catch (e) {
            // Something went wrong when trying to disable battery optimizations!
            $log.error("Something went wrong when trying to disable battery optimizations!");
            $log.error(e);
        }

        if (service.media) {
            service.media.pause();
        }

        service.isPlaying = false;
        service.duration = 0;
        service.elapsedTime = 0;
        service.isMediaLoaded = false;
        service.isMediaStopped = false;
    };

    service.start = function () {
        service.currentTrack = service.tracks[service.currentIndex];

        $log.info(service.currentTrack, service.tracks);

        if ((service.currentTrack.streamUrl.indexOf('http://') === -1) &&
            (service.currentTrack.streamUrl.indexOf('https://') === -1)) {
            Loader.hide();
            Dialog.alert('Error', 'No current stream to load.', 'OK', -1);
            return;
        }

        // Setting the albumCover image
        if (service.currentTrack.albumCover) {
            service.currentTrack.albumCover = service.currentTrack.albumCover
                .replace('100x100bb', $window.innerWidth + 'x' + $window.innerWidth + 'bb');
        }

        service.isStream = service.isRadio;

        // Some debug
        $log.debug(service.currentTrack);

        service.media = new MediaNative(
            service.currentTrack.streamUrl,
            function (success) {
                // success is media end
                service.next();
            },
            function (error) {
                // an error occured inform the user
                Dialog.alert('Error', 'something went wrong while loading the media.', 'OK', -1);
            },
            function (change) {
                // something changed, update controls & infos
                service.media.getDuration(function () {
                    service.duration = service.media._duration;
                }, function () {});
            });

        service.play();
        service.updateSeekBar();

        Loader.hide();

        service.updateMusicControls();
    };

    service.reset = function () {
        if (service.media != null) {
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

        if (service.useMusicControls) {
            MusicControls.destroy();
        }
    };

    service.destroy = function (origin) {
        $interval.cancel(service.seekbarTimer);
        if (service.media) {
            if (service.isPlaying) {
                service.media.pause();
            }
        }

        service.reset();

        if (origin === 'player') {
            service.goBack(true, true);
        }
    };

    service.openPlayer = function () {
        if (service.isInitialized) {
            service.openPlayerModal('cover');
        }
        Modal
            .fromTemplateUrl('templates/media/music/l1/player/modal/player.html', {
                scope: angular.extend($rootScope.$new(true),  {
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
        service.currentTab = tab === undefined ? 'cover' : tab;
        if (service.playerModalIsOpen) {
            return;
        }
        if (service.playerModal !== null) {
            service.playerModal.show();
            service.playerModalIsOpen = true;
        }
    };

    service.closePlayerModal = function () {
        if (!service.playerModalIsOpen) {
            return;
        }
        if (service.playerModal !== null) {
            service.playerModal.hide();
            service.playerModalIsOpen = false;
        }
    };

    service.playPause = function () {
        if (service.isPlaying) {
            service.media.pause();

            $interval.cancel(service.seekbarTimer);
        } else {
            service.media.play();
        }

        service.isPlaying = !service.isPlaying;

        service.updateMusicControls();
    };

    service.prev = function () {
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

    service.willSeek = function () {
        if (service.isPlaying) {
            service.media.pause();
            service.isPlaying = false;
        }
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
        // For now we will disable music controls for iOS!
        if (service.useMusicControls) {
            var hasPrev = true;
            var hasNext = true;
            if (service.isRadio) {
                hasPrev = false;
                hasNext = false;
            }

            if (service.currentIndex === 0) {
                hasPrev = false;
            }

            if (service.currentIndex === (service.tracks.length - 1)) {
                hasNext = false;
            }

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

            MusicControls.create(mcDictionnary,
                function () {
                    $log.debug('success');
                }, function () {
                    $log.debug('error');
                });

            MusicControls.subscribe(musicControlsEventsHandler);
            MusicControls.listen();
            MusicControls.updateIsPlaying(service.isPlaying);
        }
    };

    service.updateSeekBar = function () {
        service.seekbarTimer = $interval(function () {
            try {
                if (service.isPlaying) {
                    service.media.getCurrentPosition(
                        function () {
                            service.elapsedTime = service.media._position;
                        }, function () {});
                }

                if (!service.isRadio &&
                    service.isMediaStopped &&
                    service.isMediaLoaded) {
                    // Cancelling
                    $interval.cancel(service.seekbarTimer);
                    service.isMediaStopped = false;
                    service.next();
                }
            } catch (e) {
                // Automatically cancel if any error found!
                $interval.cancel(service.seekbarTimer);
            }
        }, 1000);
    };

    return service;
});
