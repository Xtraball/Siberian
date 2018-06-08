/*global
 App, BASE_PATH, angular
 */

angular.module("starter").controller("MusicPlaylistsController", function ($ionicHistory, $location, $q, $rootScope, $scope, $state, $stateParams,
                                                    $window, Application, MusicAlbum, MusicPlaylist,
                                                    MusicTracksLoader, MediaPlayer) {

    angular.extend($scope, {
        is_loading      : true,
        value_id        : $stateParams.value_id,
        active_tab      : "playlists"
    });

    MusicPlaylist.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;

    $scope.tracks_loader = null;

    MusicPlaylist.findPageTitle()
        .then(function(data) {
            $scope.page_title = data.page_title;
        });

    $scope.loadContent = function () {

        // retrieve playlists
        MusicPlaylist.findAll()
            .then(function (data) {

                console.log("MusicPlaylist.findAll()", data);

                // retrieve albums for each playlist
                var promises = data.playlists.reduce(function (promises, playlist) {
                    promises.push(MusicAlbum.findByPlaylist(playlist.id));
                    return promises;
                }, []);

                // synchronize all queries
                $q.all(promises).then(function (playlistsAlbums) {

                    console.log("playlistsAlbums", playlistsAlbums);

                    $scope.playlists = data.playlists.reduce(function (playlists, playlist) {

                        console.log("$scope.playlists = data.playlists.reduce(function (playlists, playlist) {",
                            playlists, playlist);

                        // add images from the 4 first albums
                        var index = playlists.length;
                        playlist.albums = playlistsAlbums[index].albums;
                        playlist.images = [];

                        if(!playlist.artworkUrl) {
                            playlist.images = playlist.albums.reduce(function (albums, album) {
                                if (albums.length < 4) {
                                    albums.push(album);
                                }
                                return albums;
                            }, []);

                            // complete with default album image if less than 4 albums in the playlist
                            for (var i = playlist.images.length; i < 4; i++) {
                                playlist.images.push({
                                    artworkUrl: data.artwork_placeholder
                                });
                            }
                        }

                        // Paging playlist images on 2 rows
                        var paged_playlist_images = [];
                        var images = [];
                        for(var j = 0; j < playlist.images.length; j++) {
                            images.push(playlist.images[j]);

                            if(images.length === 2) {
                                paged_playlist_images.push(images);
                                images = [];
                            }
                        }

                        if(images.length === 1) {
                            paged_playlist_images.push(images);
                        }

                        playlist.paged_playlist_images = paged_playlist_images;

                        playlists.push(playlist);

                        return playlists;
                    }, []);

                    MusicTracksLoader.loadTracksFromPlaylists($scope.playlists)
                        .then(function (results) {

                            console.log("MusicTracksLoader.loadTracksFromPlaylists($scope.playlists)", results);

                            $scope.tracks_loader = results.tracksLoader;
                        }).then(function() {
                            // Paging playlists on 2 rows
                            var paged_playlists = [];
                            var playlists = [];
                            for(var i = 0; i < $scope.playlists.length; i++) {
                                playlists.push($scope.playlists[i]);

                                if(playlists.length === 2) {
                                    paged_playlists.push(playlists);
                                    playlists = [];
                                }
                            }
                            if(playlists.length === 1) {
                                paged_playlists.push(playlists);
                            }

                            $scope.playlists.paged_playlists = paged_playlists;

                            $scope.is_loading = false;
                        });

                });

            },function () {
                $scope.is_loading = false;
            });
        };

    $scope.showPlaylistAlbums = function (playlist) {
        $state.go("music-playlist-albums", {
            value_id: $stateParams.value_id,
            playlist_id: playlist.id
        });
    };

    $scope.showAlbums = function (playlist) {
        $state.go("music-album-list", {
            value_id: $stateParams.value_id
        });
    };

    $scope.playAll = function () {
        if ($scope.is_loading) {
            return;
        }

        MediaPlayer.init($scope.tracks_loader, false, 0);
    };

    $scope.loadContent();

}).controller("MusicPlaylistAlbumsController", function ($ionicScrollDelegate, $location, $rootScope, $stateParams,
                                                         $scope, $state, $window, Application, MusicAlbum,
                                                         MusicPlaylist, MusicTracksLoader, MediaPlayer) {


    $scope.is_loading = true;

    $scope.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;
    window.$ionicScrollDelegate = $ionicScrollDelegate;

    $scope.tracks_loader = null;

    $scope.loadContent = function () {
        MusicPlaylist.find($stateParams.playlist_id)
            .then(function (data) {

                console.log("MusicPlaylist.find($stateParams.playlist_id)", data);

                $scope.playlist = data.playlist;
                $scope.page_title = data.playlist.name;

                MusicAlbum.findByPlaylist($stateParams.playlist_id)
                    .then(function (data) {

                        console.log("MusicAlbum.findByPlaylist($stateParams.playlist_id)", data);

                        var paged_albums = [];
                        var albums = [];
                        for(var i = 0; i < data.albums.length; i++) {
                            albums.push(data.albums[i]);

                            if(albums.length === 2) {
                                paged_albums.push(albums);
                                albums = [];
                            }
                        }

                        if(albums.length === 1) {
                            paged_albums.push(albums);
                        }

                        $scope.playlist.paged_albums = paged_albums;
                        $scope.playlist.albums = data.albums;

                        $ionicScrollDelegate.$getByHandle("albums").resize();

                        MusicTracksLoader.loadTracksFromAlbums($scope.playlist.albums)
                            .then(function (results) {

                                console.log("MusicTracksLoader.loadTracksFromAlbums($scope.playlist.albums)", results);

                                $scope.tracks_loader = results.tracksLoader;
                            });

                    }).then(function () {
                        $scope.is_loading = false;
                    });

            }, function () {
                // error
            }).then(function() {
                $scope.is_loading = false;
            });
    };

    $scope.showAlbum = function (album) {
        $location.path(album.path);
    };

    $scope.showPlaylistTracks = function () {
        $state.go("music-track-list", {
            value_id: $stateParams.value_id,
            playlist_id: $stateParams.playlist_id
        });
    };

    $scope.playAll = function () {
        if ($scope.is_loading) {
            return;
        }

        MediaPlayer.init($scope.tracks_loader, false, 0);
    };

    $scope.loadContent();

}).controller("MusicAlbumsListController", function ($ionicHistory, $location, $q, $rootScope, $stateParams, $scope,
                                                     $state, $window, Application, MusicPlaylist, MusicAlbum,
                                                     MusicTracksLoader, MediaPlayer) {


    angular.extend($scope, {
        is_loading      : true,
        value_id        : $stateParams.value_id,
        active_tab      : "albums",
        tracks_loader   : null
    });

    MusicAlbum.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;

    MusicPlaylist.findPageTitle()
        .then(function(data) {
            $scope.page_title = data.page_title;
        });

    $scope.loadContent = function () {

        // retrieve albums
        MusicAlbum.findAll()
            .then(function (data) {

                console.log("MusicAlbum.findAll()", data);

                var paged_albums = [];
                var albums = [];
                for(var i = 0; i < data.albums.length; i++) {
                    albums.push(data.albums[i]);

                    if(albums.length === 2) {
                        paged_albums.push(albums);
                        albums = [];
                    }
                }
                if(albums.length === 1) {
                    paged_albums.push(albums);
                }

                $scope.albums = data.albums;
                $scope.albums.paged_albums = paged_albums;

                MusicTracksLoader.loadTracksFromAlbums($scope.albums)
                    .then(function (results) {

                        console.log("MusicTracksLoader.loadTracksFromAlbums($scope.albums)", results);

                        $scope.tracks_loader = results.tracksLoader;
                    });

            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.showPlaylists = function (playlist) {
        $state.go("music-playlist-list", {
            value_id: $scope.value_id
        });
    };

    $scope.showAlbum = function (album) {
        $location.path(album.path);
    };

    $scope.playAll = function () {
        if ($scope.is_loading) {
            return;
        }

        MediaPlayer.init($scope.tracks_loader, false, 0);
    };

    $scope.loadContent();

}).controller("MusicAlbumViewController", function ($location, $scope, $rootScope, $stateParams, $window, Application,
                                                    MusicAlbum, MusicTrack, MusicTracksLoader, MediaPlayer) {

    $scope.is_loading = true;

    $scope.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;
    MusicTrack.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        var param = {};
        if($stateParams.album_id) {
            param.album_id = $stateParams.album_id;
        } else {
            param.track_id = $stateParams.track_id;
        }

        MusicAlbum.find(param)
            .then(function (data) {

                console.log("MusicAlbum.find(param)", data);

                $scope.album = data.album;
                $scope.page_title = data.album.name;

                MusicTrack.findByAlbum(param)
                    .then(function (data) {

                        console.log("MusicTrack.findByAlbum(param)", data);

                        $scope.album.tracks = data.tracks;

                    }, function () {
                        $scope.is_loading = false;
                    });

            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.play = function (track_index) {
        if ($scope.is_loading) {
            return;
        }

        var tracks_loader = MusicTracksLoader._buildTracksLoaderForSingleAlbum($scope.album, $scope.album.tracks);

        MediaPlayer.init(tracks_loader, false, track_index);
    };

    $scope.loadContent();

}).controller("MusicTrackListController", function ($ionicHistory, $ionicPlatform, $location, $q, $rootScope,
                                                    $stateParams, $scope, $window, Application, MusicPlaylist,
                                                    MusicAlbum, MusicTrack, MusicTracksLoader, MediaPlayer) {


    $scope.is_loading = true;
    $scope.is_loading_more_tracks = false;

    $scope.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;
    MusicTrack.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        MusicPlaylist.find($stateParams.playlist_id).then(function (data) {

            $scope.playlist = data.playlist;
            $scope.page_title = data.playlist.name;

            MusicAlbum.findByPlaylist($stateParams.playlist_id).then(function (data) {

                MusicTracksLoader.loadTracksFromAlbums(data.albums).then(function (results) {
                    $scope.tracks_loader = results.tracksLoader;
                    $scope.tracks = results.tracksLoaded;
                }).finally(function () {
                    $scope.is_loading = false;
                });

            }, function () {
                // error
                $scope.is_loading = false;
            });

        }, function () {
            $scope.is_loading = false;
        });
    };

    $scope.showPlaylistAlbums = function () {
        $ionicHistory.goBack();
    };

    $scope.play = function ($trackIndex) {
        MediaPlayer.init($scope.tracks_loader, false, $trackIndex);
    };

    $scope.enable_load_onscroll = true;

    $scope.loadMore = function () {
        if ($scope.tracks_loader) {
            $scope.is_loading_more_tracks = true;
            return $scope.tracks_loader.loadMore(50).then(function (results) {
                // add more tracks
                $scope.tracks = $scope.tracks.concat(results.tracksLoaded);
                $scope.is_loading_more_tracks = false;
            });
        }
    };

    $scope.loadContent();

});