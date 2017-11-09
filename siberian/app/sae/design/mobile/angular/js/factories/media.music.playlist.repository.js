"use strict";

App.factory('MediaMusicPlaylist', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function(playlist_id) {

        if (!this.value_id) {
            console.error('value_id is not defined.');
            return;
        }

        return $http({
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

        return $http({
            method: 'GET',
            url: Url.get("media/mobile_api_music_playlist/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findPageTitle = function() {
        return $http({
            method: 'GET',
            url: Url.get("media/mobile_api_music_playlist/getpagetitle", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
