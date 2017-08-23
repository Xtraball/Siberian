/* global
    angular
 */

/**
 * MusicPlaylist
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('MusicPlaylist', function ($pwaRequest) {
    var factory = {
        value_id: null,
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param value_id
     */
    factory.preFetch = function (page) {
        factory.findAll();
        /** @todo prefetch, when findall is done, pre-fetch albums, tracks ... */
    };

    factory.findAll = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::MusicPlaylist.findAll] missing value_id');
        }

        return $pwaRequest.get('media/mobile_api_music_playlist/findall', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.find = function (playlist_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::MusicPlaylist.find] missing value_id');
        }

        return $pwaRequest.get('media/mobile_api_music_playlist/find', {
            urlParams: {
                value_id: this.value_id,
                playlist_id: playlist_id
            }
        });
    };

    factory.findPageTitle = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::MusicPlaylist.findPageTitle] missing value_id');
        }

        return $pwaRequest.get('media/mobile_api_music_playlist/getpagetitle', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    return factory;
});


/**
 * MusicAlbum
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('MusicAlbum', function ($pwaRequest) {
    var factory = {
        value_id: null
    };

    factory.find = function (element) {
        if (!this.value_id || !element) {
            return $pwaRequest.reject('[Factory::MusicAlbum.find] missing value_id and/or element');
        }

        var params = {
            value_id: this.value_id
        };

        if (element.album_id) {
            params.album_id = element.album_id;
        } else {
            params.track_id = element.track_id;
        }

        return $pwaRequest.get('media/mobile_api_music_album/find', {
            urlParams: params
        });
    };

    factory.findAll = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::MusicAlbum.findAll] missing value_id');
        }

        return $pwaRequest.get('media/mobile_api_music_album/findall', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    factory.findByPlaylist = function (playlist_id) {
        if (!this.value_id || !playlist_id) {
            return $pwaRequest.reject('[Factory::MusicAlbum.findByPlaylist] missing value_id and/or playlist_id');
        }

        return $pwaRequest.get('media/mobile_api_music_album/findbyplaylist', {
            urlParams: {
                value_id: this.value_id,
                playlist_id: playlist_id
            }
        });
    };

    return factory;
});

/**
 * MusicTrack
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('MusicTrack', function ($pwaRequest) {
    var factory = {
        value_id: null
    };

    factory.findByAlbum = function (element) {
        if (!this.value_id || !element) {
            return $pwaRequest.reject('[Factory::MusicTrack.findByAlbum] missing value_id and/or element');
        }

        var params = {
            value_id: this.value_id
        };

        if (element.album_id) {
            params.album_id = element.album_id;
        } else {
            params.track_id = element.track_id;
        }

        return $pwaRequest.get('media/mobile_api_music_track/findbyalbum', {
            urlParams: params
        });
    };

    return factory;
});
