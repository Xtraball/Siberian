App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/gallery/view", {
        controller: 'GalleryViewController',
        template: '<div sb-image-gallery gallery="gallery"></div>',
        code: "gallery"
    });

}).controller('GalleryViewController', function($scope, ImageGallery) {

    $scope.gallery = ImageGallery;

});