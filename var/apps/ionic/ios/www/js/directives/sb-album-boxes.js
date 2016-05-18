App.directive('sbAlbumsBoxes', function () {
    return {
        restrict: 'A',
        templateUrl: 'templates/media/music/l1/album/boxes.html',
        scope: {
            paged_albums: '=pagedAlbums',
            onAlbumSelected: '&'
        }
    };
});