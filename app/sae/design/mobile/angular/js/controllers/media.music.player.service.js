"use strict";

// HTML Audio and Video DOM Reference: http://www.w3schools.com/tags/ref_av_dom.asp

App.service('MediaMusicPlayerService', function ($location, $routeParams, Url) {

    this.playing = false;

    // html5 audio element
    this.audio = null;

    this.tracks = [];
    this.currentTrackIndex = null;

    this.random = false;
    this.repeat = false;
    this.repeatTrack = false;

    this.tracksLoader = null;
    this.value_id = null;

    var service = this;

    service.init = function (document) {
        if (service.audio === null) {
            service.value_id = $routeParams.value_id;
            console.info('Init audio.');
            service.audio = document.createElement('audio');
            service.audio.src = null;
            service.audio.addEventListener('ended', function () {
                service.nextAuto();
            });
        }
    };

    service.openPlayer = function (replace) {
        var redirect = $location.path(Url.get("media/mobile_gallery_music_player/index", {
            value_id: service.value_id
        }));
        if (replace){
            redirect.replace();
        }
    };

    service.getRandomTrackNumber = function () {
        if (this.tracks.length === 0) {
            return null;
        } else {
            return Math.floor(Math.random() * (this.tracks.length));
        }
    }

    service.getCurrentTrack = function () {
        if (service.currentTrackIndex === null) {
            return null;
        } else {
            return service.tracks[service.currentTrackIndex];
        }
    };
    service.switchTo = function (index) {
        if (index < service.tracks.length) {
            service.currentTrackIndex = index;
            service.audio.src = service.tracks[service.currentTrackIndex].streamUrl;
            if (service.currentTrackIndex >= (service.tracks.length - 1 - 2)) {
                // in the last 2 songs, try to load more songs
                service.loadMore(50);
            }
        } else {
            service.currentTrackIndex = null;
            service.audio.src = null;
        }
    };
    service.nextAuto = function () {
        if (service.tracks.length !== 0) {
            if (service.repeatTrack) {
                service.audio.currentTime = 0;
                service.audio.play();
            } else if (service.random) {
                var trackIndex = service.getRandomTrackNumber();
                // play next song
                service.switchTo(trackIndex);
                service.audio.play();
            } else {
                var playNext = true;
                if (service.currentTrackIndex !== null && service.currentTrackIndex === (service.tracks.length - 1)) {
                    // last track
                    if (!service.repeat) {
                        playNext = false;
                    }
                }
                if (playNext) {
                    service.next();
                } else {
                    service.currentTrackIndex = null;
                    service.audio.pause();
                }
            }

        }
    };
    service.next = function () {
        if (service.tracks.length !== 0) {
            if (service.currentTrackIndex === null || service.currentTrackIndex === (service.tracks.length - 1)) {
                // play first song
                service.switchTo(0);
            } else {
                // play next song
                service.switchTo(service.currentTrackIndex + 1);
            }
            service.audio.play();
        } else {
            service.audio.pause();
        }
    };
    service.previous = function () {
        if (service.tracks.length !== 0) {
            if (service.currentTrackIndex === null || service.currentTrackIndex === 0) {
                // play last song
                service.switchTo(service.tracks.length - 1);
            } else {
                // play previous song
                service.switchTo(service.currentTrackIndex - 1);
            }
            service.audio.play();
        } else {
            service.audio.pause();
        }
    };
    service.play = function () {
        if (service.audio === null) {
            return console.error('Service has not been initialized.');
        }
        console.info('Play.');
        if (service.currentTrackIndex === null && service.tracks.length !== 0) {
            // play first song
            service.switchTo(0);
        }

        service.audio.play();
        service.playing = true;
    };
    service.pause = function () {
        console.info('Pause.');
        if (service.audio === null) {
            return console.error('Service has not been initialized.');
        }
        service.audio.pause();
        service.playing = false;
    };

    service.addTracks = function (tracks, replaceAll) {

        if (replaceAll) {
            // add number to tracks
            tracks.reduce(function (i, track) {
                track.number = i;
                return i + 1;
            }, 1);

            service.tracks = tracks;
        } else {
            // add number to tracks
            tracks.reduce(function (i, track) {
                track.number = i;
                return i + 1;
            }, service.tracks.length + 1);

            service.tracks = service.tracks.concat(tracks);
        }
    };

    service.playTracks = function (tracksLoader, index, forceToOpenPlayer, replaceUrlOnRedirect) {

        var wasVisible = service.isPlayerLinkVisible();

        service.tracksLoader = tracksLoader;

        var tracks = tracksLoader.tracks;

        // replace all tracks    
        service.addTracks(tracks, true);

        if (!isNaN(index)) {
            service.switchTo(index);
            service.play();
        }
        if (forceToOpenPlayer || !wasVisible) {
            service.openPlayer(replaceUrlOnRedirect);
        }
    };
    service.clear = function () {
        // clear tracks
        service.setTracks([]);
    };
    service.setTracks = function (tracks) {
        // pause current track
        service.pause();
        // clear tracks
        service.tracks = tracks;
        service.currentTrackIndex = null;
        service.audio.src = '';
    };
    service.getTracks = function () {
        return service.tracks;
    };
    service.isPlaying = function () {
        if (service.audio && service.tracks.length !== 0) {
            return !service.audio.paused;
        } else {
            return false;
        }
    };
    service.isLoading = function () {
        if (service.audio && service.tracks.length !== 0) {
            return service.isPlaying() && (!service.audio || !service.audio.duration);
        } else {
            return false;
        }
    };

    service.isPlayerLinkVisible = function () {
        return service.tracks.length !== 0 && $location.path().indexOf('mobile_gallery_music_player/index') === -1;
    };

    service.loadMore = function (numberToLoad) {
        if (service.tracksLoader) {
            return service.tracksLoader.loadMore(numberToLoad).then(function (results) {
                // add loaded tracks    
                service.addTracks(results.tracksLoaded, false);
            });
        }
    };

    return {
        init: service.init,
        control: {
            play: service.play,
            pause: service.pause,
            next: service.next,
            previous: service.previous,
            switchTo: service.switchTo,
            toggleRepeat: function () {
                service.repeat = !service.repeat;
            },
            toggleRandom: function () {
                service.random = !service.random;
            },
            toggleRepeatTrack: function () {
                service.repeatTrack = !service.repeatTrack;
            },
            updatePlayerLinkVisibility: service.updatePlayerLinkVisibility
        },
        status: function () {
            return {
                playing: service.isPlaying() && !service.isLoading(),
                pausing: !service.isPlaying() && !service.isLoading(),
                loading: service.isLoading(),
                repeat: service.repeat,
                repeatTrack: service.repeatTrack,
                random: service.random
            };
        },
        isPlayerLinkVisible: service.isPlayerLinkVisible,
        playTracks: service.playTracks,
        getTracks: service.getTracks,
        clear: service.clear,
        getCurrentTrack: service.getCurrentTrack,
        audio: function () {
            return service.audio
        },
        loadMore: service.loadMore,
        openPlayer: service.openPlayer
    };


});