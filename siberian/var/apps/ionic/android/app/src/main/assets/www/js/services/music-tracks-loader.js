/*global
    angular
 */
angular.module("starter").service('MusicTracksLoader', function ($q, $stateParams, MusicTrack) {

    MusicTrack.value_id = $stateParams.value_id;

    var service = {};

    service._filterDuplicatedAlbums = function (albums, albumsIds) {

        if (!albumsIds) {
            albumsIds = [];
        }

        // filter duplicated
        albums = albums.reduce(function (albums, album) {
            if (albumsIds.indexOf(album.id) === -1) {
                albumsIds.push(album.id);
                albums.push(album);
            }
            return albums;
        }, []);

        return albums;
    };

    service.loadTracksFromAlbums = function (albums) {

        var deferred = $q.defer();

        var albumsIds = [];

        // filter duplicated
        albums = service._filterDuplicatedAlbums(albums, albumsIds);

        var tracksLoader = service._buildTracksLoader(albums);

        // read the first tracks
        deferred.resolve(service.readNextTracks(tracksLoader, 50));

        return deferred.promise;

    };

    service.loadTracksFromPlaylists = function (playlists) {

        var deferred = $q.defer();

        var albumsIds = [];

        // get albums from playlists
        var albums = playlists.reduce(function (albums, playlist) {

            // filter duplicated
            var playlistAlbums = service._filterDuplicatedAlbums(playlist.albums, albumsIds);

            // add to list
            albums = albums.concat(playlistAlbums);
            return albums;
        }, []);

        var tracksLoader = service._buildTracksLoader(albums);

        // read the first tracks
        deferred.resolve(service.readNextTracks(tracksLoader, 50));

        return deferred.promise;

    };

    service._buildTracksLoader = function (albums) {

        console.log("_buildTracksLoader", albums);

        return {
            albums: albums,
            tracks: [],
            albumsLoaded: 0,
            errorOccured: false,
            fullyLoaded: function () {
                return this.errorOccured || this.albums.length === this.albumsLoaded;
            },
            loadMore: function (quantity) {
                return service.readNextTracks(this, quantity);
            }
        };
    };


    service._buildTracksLoaderForSingleAlbum = function (album, tracks) {
        var tracksLoader = service._buildTracksLoader([album]);
        tracksLoader.tracks = tracks;
        tracksLoader.albumsLoaded = 1;

        return tracksLoader;
    };

    service.loadSingleTrack = function (track) {
        var tracksLoader = service._buildTracksLoader([]);
        tracksLoader.tracks = [track];
        tracksLoader.albumsLoaded = 0;

        return tracksLoader;
    };
    /**
     * Load the next tracks.
     *
     * Asynchronous service, returns a promise.
     *
     */
    service.readNextTracks = function (tracksLoader, quantityToLoad) {

        var tracksLoaded = [];

        return service.readNextTracksRecursive(tracksLoaded, tracksLoader, quantityToLoad);

    };

    /**
     * Load the next tracks (recursive).
     *
     * Asynchronous service, returns a promise.
     *
     * Recursive call until the number of tracks to load is reached or all albums have been loaded.
     */
    service.readNextTracksRecursive = function (tracksLoaded, tracksLoader, quantityToLoad) {

        var deferred = $q.defer();

        var maxAlbumsToLoadAtOnce = Math.ceil((quantityToLoad - tracksLoaded.length) / 15);
        if (maxAlbumsToLoadAtOnce === 0) {
            maxAlbumsToLoadAtOnce = 1;
        }

        service.readNextAlbumsTracks(tracksLoader, maxAlbumsToLoadAtOnce).then(function (result) {

                tracksLoaded = tracksLoaded.concat(result.tracksLoaded);

                if (!tracksLoader.fullyLoaded() && tracksLoaded.length < quantityToLoad) {

                    // delegate the resolution of the deferred to the next recursive method
                    deferred.resolve(service.readNextTracksRecursive(tracksLoaded, tracksLoader, quantityToLoad, maxAlbumsToLoadAtOnce));

                } else {
                    // last call, resolve all the recursion chain
                    deferred.resolve({
                        tracksLoader: tracksLoader,
                        tracksLoaded: tracksLoaded
                    });

                }

            },
            function (err) {
                console.error('Error while loading tracks.', err);
                deferred.reject(err);
                tracksLoader.errorOccured = true;
            });

        return deferred.promise;

    };

    /**
     * Load the next albums tracks (max : maxAlbumsToLoad).
     *
     * Asynchronous service, returns a promise.
     */
    service.readNextAlbumsTracks = function (tracksLoader, maxAlbumsToLoad) {

        var deferred = $q.defer();

        var promises = [];
        for (var i = 0; i < maxAlbumsToLoad && !tracksLoader.fullyLoaded(); i++, tracksLoader.albumsLoaded++) {
            var album = tracksLoader.albums[tracksLoader.albumsLoaded];
            var param = {};
            if(album.element == "album") {
                param.album_id = album.id;
            } else {
                param.track_id = album.id;
            }
            promises.push(MusicTrack.findByAlbum(param));
        }

        // synchronize all queries
        try {
            $q.all(promises)
                .then(function (tracksResponses) {

                        var tracksLoaded = tracksResponses.reduce(function (tracks, tracksResponse) {
                            tracks = tracks.concat(tracksResponse.tracks);

                            return tracks;
                        }, []);

                        tracksLoader.tracks = tracksLoader.tracks.concat(tracksLoaded);

                        deferred.resolve({
                            tracksLoader: tracksLoader,
                            tracksLoaded: tracksLoaded
                        });
                    },
                    function (err) {
                        console.error('Error while loading tracks.', err);
                        deferred.reject(err);
                    }).finally(function () {});
        } catch(e) {
            console.error(e.message);
        }


        return deferred.promise;

    };

    return service;

});