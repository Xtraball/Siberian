App.config(function($stateProvider) {

    $stateProvider.state('image-list', {
        url: BASE_PATH+"/media/mobile_gallery_image_list/index/value_id/:value_id",
        templateUrl: 'templates/media/image/l1/list.html',
        controller: 'ImageListController'
    });

}).controller('ImageListController', function($scope, $stateParams, $timeout, $translate, Url, Image, ionGalleryConfig) {

    ionGalleryConfig.action_label = $translate.instant("Done");

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.can_load_more = true;
    $scope.images = new Array();
    $scope.collection = new Array();
    $scope.show_galleries = false;
    $scope.value_id = Image.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        if($scope.is_loading) return;

        $scope.is_loading = true;

        Image.findAll().success(function(data) {
            $scope.galleries = data.galleries;
            if($scope.galleries.length) {
                $scope.is_loading = false;
                $scope.showGallery($scope.galleries[0]);
            }
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.showGallery = function(gallery) {

        $scope.show_galleries = false;

        if($scope.current_gallery && $scope.current_gallery.id == gallery.id || $scope.is_loading) return;

        $scope.can_load_more = true;
        $scope.collection = new Array();
        $scope.current_gallery = gallery;

        $scope.loadGallery();

    };

    $scope.loadGallery = function() {
        $scope.is_loading = true;

        var offset = 0;

        if($scope.collection.length) {
            offset = $scope.collection[$scope.collection.length - 1].offset;
            if ($scope.current_gallery.type == "custom") offset++;
        }

        Image.find($scope.current_gallery, offset).success(function(data) {
            for(var i = 0; i < data.collection.length; i++) {
                $scope.collection.push(data.collection[i]);
            }
            $scope.can_load_more = data.collection.length > 0 && data.show_load_more;

        }).error(function(err) {
        }).finally(function() {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });


    };

    $scope.toggleGalleries = function() {
        $scope.show_galleries = !$scope.show_galleries;
    };

    $scope.loadContent();

});