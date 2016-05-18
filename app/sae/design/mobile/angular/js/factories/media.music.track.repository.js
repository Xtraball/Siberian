"use strict";

App.factory('MediaMusicTrack', function ($rootScope, $http, Url) {

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

        return $http({
            method: 'GET',
            url: Url.get("media/mobile_api_music_track/findbyalbum", params),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    return factory;
});