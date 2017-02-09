// Playlist
App.factory('MusicPlaylist', function($sbhttp, $rootScope, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function(playlist_id) {

        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_api_music_playlist/find", {value_id: this.value_id, playlist_id: playlist_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_api_music_playlist/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findPageTitle = function() {
        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_api_music_playlist/getpagetitle", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});

// Album
App.factory('MusicAlbum', function ($sbhttp, $rootScope, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function (element) {

        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }
        if (!element) {
            console.error('album_id is not defined.');
            return;
        }

        var params = {
            value_id: this.value_id
        };
        if(element.album_id) {
            params.album_id = element.album_id;
        } else {
            params.track_id = element.track_id;
        }

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_api_music_album/find", params),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.findAll = function() {

        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_api_music_album/findall", {value_id: this.value_id,}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findByPlaylist = function (playlist_id) {

        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }
        if (!playlist_id) {
            console.error('playlist_id is not defined.');
            return;
        }

        var url = Url.get("media/mobile_api_music_album/findbyplaylist", {
            value_id: this.value_id,
            playlist_id: playlist_id
        });

        return $sbhttp({
            method: 'GET',
            url: url,
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    return factory;
});

// Track
App.factory('MusicTrack', function ($sbhttp, $rootScope, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findByAlbum = function (element) {

        console.log("element: ", element);
        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }

        if (!element) {
            console.error('album_id is not defined.');
            return;
        }

        var params = {
            value_id: this.value_id
        };
        if(element.album_id) {
            params.album_id = element.album_id;
        } else {
            params.track_id = element.track_id;
        }

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_api_music_track/findbyalbum", params),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    return factory;
});
