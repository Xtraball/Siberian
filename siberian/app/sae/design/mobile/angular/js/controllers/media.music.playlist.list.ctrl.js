"use strict";

App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/media/mobile_gallery_music_playlists/index/value_id/:value_id", {
        controller: 'MediaGalleryMusicPlaylistsController',
        templateUrl: BASE_URL + "/media/mobile_gallery_music_playlists/template",
        code: "media-gallery-music-page media-gallery-music-playlists"
    });

}).controller('MediaGalleryMusicPlaylistsController', function ($window, $scope, $routeParams, $location, $q, Url,
    MediaMusicPlaylist, MediaMusicAlbum, MediaMusicTracksLoaderService, MediaMusicPlayerService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = $routeParams.value_id;
    MediaMusicPlaylist.value_id = $routeParams.value_id;
    MediaMusicAlbum.value_id = $routeParams.value_id;

    MediaMusicPlaylist.findPageTitle().success(function(data) {
        $scope.page_title = data.page_title;
    });

    $scope.loadContent = function () {

        // retrieve playlists
        MediaMusicPlaylist.findAll().success(function (data) {

            // retrieve albums for each playlist
            var promises = data.playlists.reduce(function (promises, playlist) {
                promises.push(MediaMusicAlbum.findByPlaylist(playlist.id));
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

                    playlists.push(playlist);
                    return playlists;
                }, []);

            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    }

    $scope.showPlaylistAlbums = function (playlist) {
        $location.path(Url.get("media/mobile_gallery_music_playlistalbums/index", {
            value_id: $routeParams.value_id,
            playlist_id: playlist.id
        }));
    };

    $scope.showAlbums = function (playlist) {
        $location.path(Url.get("media/mobile_gallery_music_albums/index", {
            value_id: $routeParams.value_id
        }));
    };

    $scope.playAll = function () {

        if ($scope.is_loading) return;
        
        $scope.is_loading = true;

        MediaMusicTracksLoaderService.loadTracksFromPlaylists($scope.playlists).then(function (results) {

            if(Application.handle_audio_player) {

                var albums = [];
                for(var playlist_offset in $scope.playlists) {
                    for(var album in $scope.playlists[playlist_offset].albums) {
                        albums.push($scope.playlists[playlist_offset].albums[album]);
                    }
                }

                $window.audio_player_data = JSON.stringify(
                    {
                        tracks: results.tracksLoader.tracks,
                        albums: albums,
                        trackIndex: 0
                    }
                );
                Application.call("openAudioPlayer", $window.audio_player_data);

            } else {

                MediaMusicPlayerService.init(document);
                // play all tracks (starting from first one)
                MediaMusicPlayerService.playTracks(results.tracksLoader, 0);

            }

        }).finally(function () {
            $scope.is_loading = false;
        });

    };

    $scope.loadContent();

});