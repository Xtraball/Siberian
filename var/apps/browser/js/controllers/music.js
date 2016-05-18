App.config(function ($stateProvider) {

    $stateProvider.state('music-playlist-list', {
        url: BASE_PATH + "/media/mobile_gallery_music_playlists/index/value_id/:value_id",
        controller: "MusicPlaylistsController",
        templateUrl: "templates/media/music/l1/playlist/list.html"
    }).state('music-playlist-albums', {
        url: BASE_PATH + "/media/mobile_gallery_music_playlistalbums/index/value_id/:value_id/playlist_id/:playlist_id",
        controller: "MusicPlaylistAlbumsController",
        templateUrl: "templates/media/music/l1/playlist/albums.html"
    }).state('music-album-list', {
        url: BASE_PATH + "/media/mobile_gallery_music_albums/index/value_id/:value_id",
        controller: 'MusicAlbumsListController',
        templateUrl: "templates/media/music/l1/album/list.html"
    }).state('music-album-view', {
        url: BASE_PATH + "/media/mobile_gallery_music_album/index/value_id/:value_id/album_id/:album_id",
        controller: 'MusicAlbumViewController',
        templateUrl: "templates/media/music/l1/album/view.html"
    }).state('music-album-view-track', {
        url: BASE_PATH + "/media/mobile_gallery_music_album/index/value_id/:value_id/track_id/:track_id",
        controller: 'MusicAlbumViewController',
        templateUrl: "templates/media/music/l1/album/view.html"
    }).state('music-track-list', {
        url: BASE_PATH + "/media/mobile_gallery_music_playlisttracks/index/value_id/:value_id/playlist_id/:playlist_id",
        controller: 'MusicTrackListController',
        templateUrl: "templates/media/music/l1/track/list.html"
    });

}).controller('MusicPlaylistsController', function ($location, $q, $rootScope, $scope, $state, $stateParams, $window, Application, MusicAlbum, MusicPlaylist, MusicTracksLoader, MediaPlayer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;

    $scope.tracks_loader = null;

    MusicPlaylist.findPageTitle().success(function(data) {
        $scope.page_title = data.page_title;
    });

    $scope.loadContent = function () {

        // retrieve playlists
        MusicPlaylist.findAll().success(function (data) {

            // retrieve albums for each playlist
            var promises = data.playlists.reduce(function (promises, playlist) {
                promises.push(MusicAlbum.findByPlaylist(playlist.id));
                return promises;
            }, []);

            // synchronize all queries
            $q.all(promises).then(function (playlistsAlbums) {

                $scope.playlists = data.playlists.reduce(function (playlists, playlist) {
                    // add images from the 4 first albums
                    var index = playlists.length;
                    playlist.albums = playlistsAlbums[index].data.albums;
                    playlist.images = new Array();

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
                    var paged_playlist_images = new Array();
                    var images = new Array();
                    for(var j = 0; j < playlist.images.length; j++) {
                        images.push(playlist.images[j]);

                        if(images.length == 2) {
                            paged_playlist_images.push(images);
                            images = new Array();
                        }
                    }

                    if(images.length == 1) paged_playlist_images.push(images);

                    playlist.paged_playlist_images = paged_playlist_images;

                    playlists.push(playlist);

                    return playlists;
                }, []);

                MusicTracksLoader.loadTracksFromPlaylists($scope.playlists).then(function (results) {
                    $scope.tracks_loader = results.tracksLoader;
                }).finally(function() {
                    // Paging playlists on 2 rows
                    var paged_playlists = new Array();
                    var playlists = new Array();
                    for(var i = 0; i < $scope.playlists.length; i++) {
                        playlists.push($scope.playlists[i]);

                        if(playlists.length == 2) {
                            paged_playlists.push(playlists);
                            playlists = new Array();
                        }
                    }
                    if(playlists.length == 1) paged_playlists.push(playlists);

                    $scope.playlists.paged_playlists = paged_playlists;

                    $scope.is_loading = false;
                });

            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.showPlaylistAlbums = function (playlist) {
        $state.go("music-playlist-albums", {value_id: $stateParams.value_id, playlist_id: playlist.id});
    };

    $scope.showAlbums = function (playlist) {
        $state.go("music-album-list", {value_id: $stateParams.value_id});
    };

    $scope.playAll = function () {
        if ($scope.is_loading) return;

        MediaPlayer.init($scope.tracks_loader, false, 0);
    };

    $scope.loadContent();

}).controller('MusicPlaylistAlbumsController', function ($ionicScrollDelegate, $location, $rootScope, $stateParams, $scope, $state, $window, Application, MusicAlbum, MusicPlaylist, MusicTracksLoader, MediaPlayer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;
    window.$ionicScrollDelegate = $ionicScrollDelegate;

    $scope.tracks_loader = null;

    $scope.loadContent = function () {
        MusicPlaylist.find($stateParams.playlist_id).success(function (data) {

            $scope.playlist = data.playlist;
            $scope.page_title = data.playlist.name;

            MusicAlbum.findByPlaylist($stateParams.playlist_id).success(function (data) {

                var paged_albums = new Array();
                var albums = new Array();
                for(var i = 0; i < data.albums.length; i++) {
                    albums.push(data.albums[i]);

                    if(albums.length == 2) {
                        paged_albums.push(albums);
                        albums = new Array();
                    }
                }
                if(albums.length == 1) paged_albums.push(albums);

                $scope.playlist.paged_albums = paged_albums;
                $scope.playlist.albums = data.albums;

                $ionicScrollDelegate.$getByHandle("albums").resize();

                MusicTracksLoader.loadTracksFromAlbums($scope.playlist.albums).then(function (results) {
                    $scope.tracks_loader = results.tracksLoader;
                });

            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.showAlbum = function (album) {
        $location.path(album.path);
    };

    $scope.showPlaylistTracks = function () {
        $state.go('music-track-list', { value_id: $stateParams.value_id, playlist_id: $stateParams.playlist_id });
    };

    $scope.playAll = function () {
        if ($scope.is_loading) return;

        MediaPlayer.init($scope.tracks_loader, false, 0);
    };

    $scope.loadContent();

}).controller('MusicAlbumsListController', function ($location, $q, $rootScope, $stateParams, $scope, $state, $window, Application, MusicPlaylist, MusicAlbum, MusicTracksLoader, MediaPlayer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;

    $scope.tracks_loader = null;

    MusicPlaylist.findPageTitle().success(function(data) {
        $scope.page_title = data.page_title;
    });

    $scope.loadContent = function () {

        // retrieve albums
        MusicAlbum.findAll().success(function (data) {

            var paged_albums = new Array();
            var albums = new Array();
            for(var i = 0; i < data.albums.length; i++) {
                albums.push(data.albums[i]);

                if(albums.length == 2) {
                    paged_albums.push(albums);
                    albums = new Array();
                }
            }
            if(albums.length == 1) paged_albums.push(albums);

            $scope.albums = data.albums;
            $scope.albums.paged_albums = paged_albums;

            MusicTracksLoader.loadTracksFromAlbums($scope.albums).then(function (results) {
                $scope.tracks_loader = results.tracksLoader;
            });

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.showPlaylists = function (playlist) {
        $state.go('music-playlist-list', { value_id: $scope.value_id });
    };

    $scope.showAlbum = function (album) {
        $location.path(album.path);
    };

    $scope.playAll = function () {
        if ($scope.is_loading) return;

        MediaPlayer.init($scope.tracks_loader, false, 0);
    };

    $scope.loadContent();

}).controller('MusicAlbumViewController', function ($location, $scope, $rootScope, $stateParams, $window, Application, MusicAlbum, MusicTrack, MusicTracksLoader, MediaPlayer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

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
        MusicAlbum.find(param).success(function (data) {
            $scope.album = data.album;
            $scope.page_title = data.album.name;

            MusicTrack.findByAlbum(param).success(function (data) {

                $scope.album.tracks = data.tracks;

            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.play = function (track_index) {
        if ($scope.is_loading) return;

        var tracks_loader = MusicTracksLoader._buildTracksLoaderForSingleAlbum($scope.album, $scope.album.tracks);

        MediaPlayer.init(tracks_loader, false, track_index);
    };

    $scope.loadContent();

}).controller('MusicTrackListController', function ($ionicHistory, $ionicPlatform, $location, $q, $rootScope, $stateParams, $scope, $window, Application, MusicPlaylist, MusicAlbum, MusicTrack, MusicTracksLoader, MediaPlayer, Url) {

    $scope.$on("connectionStateChange", function (event, args) {
        if (args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.is_loading_more_tracks = false;

    $scope.value_id = $stateParams.value_id;
    MusicPlaylist.value_id = $stateParams.value_id;
    MusicAlbum.value_id = $stateParams.value_id;
    MusicTrack.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        MusicPlaylist.find($stateParams.playlist_id).success(function (data) {

            $scope.playlist = data.playlist;
            $scope.page_title = data.playlist.name;

            MusicAlbum.findByPlaylist($stateParams.playlist_id).success(function (data) {

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

        }).error(function () {
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