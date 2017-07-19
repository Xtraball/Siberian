/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("ImageListController", function($scope, $stateParams, $timeout, $translate, Url,
                                                                     Image, ionGalleryConfig) {

    angular.extend($scope, {
        is_loading      : false,
        can_load_more   : true,
        images          : [],
        collection      : [],
        show_galleries  : false,
        value_id        : $stateParams.value_id,
        card_design     : false
    });

    ionGalleryConfig.action_label = $translate.instant("Done");

    Image.setValueId($stateParams.value_id);

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Image.findAll()
            .then(function(data) {

                $scope.galleries = data.galleries;
                if($scope.galleries.length) {
                    $scope.is_loading = false;
                    $scope.showGallery($scope.galleries[0]);
                }
                $scope.page_title = data.page_title;

            }).then(function() {

                $scope.is_loading = false;

            });
    };

    $scope.showGallery = function(gallery) {

        $scope.show_galleries = false;
        $scope.button_label = gallery.name;

        if($scope.current_gallery && ($scope.current_gallery.id === gallery.id) || $scope.is_loading) {
            return;
        }

        $scope.can_load_more = true;
        $scope.collection = [];
        $scope.current_gallery = gallery;

        $scope.loadGallery();

    };

    $scope.loadGallery = function() {
        $scope.is_loading = true;

        var offset = 0;

        if($scope.collection.length) {
            offset = $scope.collection[$scope.collection.length - 1].offset;
            if ($scope.current_gallery.type == "custom") {
                offset++;
            }
        }

        Image.find($scope.current_gallery, offset)
            .then(function(data) {
                for(var i = 0; i < data.collection.length; i++) {
                    $scope.collection.push(data.collection[i]);
                }
                $scope.can_load_more = data.collection.length > 0 && data.show_load_more;

            }).then(function() {
                $scope.is_loading = false;
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });


    };

    $scope.toggleGalleries = function() {
        $scope.show_galleries = !$scope.show_galleries;
    };

    $scope.loadContent();

});