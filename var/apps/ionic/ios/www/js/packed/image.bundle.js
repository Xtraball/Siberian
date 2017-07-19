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

});;/*global
 App, angular
 */

/**
 * Image
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Image", function($pwaRequest) {
    
    var factory = {
        value_id            : null,
        displayed_per_page  : 0,
        extendedOptions     : {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };
    
    factory.findAll = function() {
        
        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Image.findAll] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if(payload !== false) {

            return $pwaRequest.resolve(payload);

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("media/mobile_gallery_image_list/findall", angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));

        }
        

    };
    
    factory.find = function(item, offset) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Image.find] missing value_id");
        }

        return $pwaRequest.get("media/mobile_gallery_image_view/find", {
            urlParams: {
                value_id        : this.value_id,
                gallery_id      : item.id,
                offset          : offset

            }
        });
    };
    
    return factory;
});
