"use strict";

App.directive('albumsBoxes', function () {
    return {
        restrict: 'A',
        templateUrl: BASE_URL + '/media/mobile_gallery_music_albumsboxes/template',
        scope: {
            albums: '=',
            onAlbumSelected: '&'
        },
        link: function ($scope, element, attrs) {


        }
    };
});