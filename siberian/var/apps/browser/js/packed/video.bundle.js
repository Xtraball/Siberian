/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("VideoListController", function($scope, $stateParams, Video, Youtube) {

    angular.extend($scope, {
        is_loading: false,
        enable_load_onscroll: true,
        value_id: $stateParams.value_id,
        offset: null,
        collection: [],
        show_galleries: false,
        factory: Video,
        galleries: []
    });

    Video.setValueId($stateParams.value_id);

    var itemsAlreadyLoaded = function(items) {
        if(items !== undefined) {
            return items.every(function(item){
                return $scope.collection.some(function(c){
                    return (c.video_id == item.video_id);
                });
            });
        }

    };

    $scope.loadContent = function() {
        $scope.offset =null;
        if($scope.is_loading) {
            return;
        }

        $scope.is_loading = true;

        Video.findAll()
            .then(function(data) {
                Youtube.key = data.youtube_key;
                $scope.galleries = data.collection;
                if($scope.galleries.length) {
                    $scope.is_loading = false;
                    $scope.showGallery($scope.galleries[0]);

                }
                $scope.page_title = data.page_title;
            }).then(function() {
                $scope.is_loading = false;

            });

    };

    $scope.toggleGalleries = function() {
        $scope.show_galleries = !$scope.show_galleries;
    };

    $scope.showGallery = function(gallery) {

        $scope.show_galleries = false;

        if($scope.current_gallery && ($scope.current_gallery.id == gallery.id)){

           if($scope.is_loading) {
               return;
           }
        } else {
            $scope.offset = null;
            $scope.is_loading = true;
        }

        $scope.can_load_more = true;
        $scope.collection = [];
        $scope.current_gallery = gallery;
        $scope.loadGallery();
        $scope.offset = null;

    };

    $scope.loadGallery = function() {

        var offset = 0;

        if($scope.collection.length) {
            offset = $scope.collection[$scope.collection.length - 1].offset;
        }
        

        $scope.current_gallery.current_offset = ++offset;

        if($scope.current_gallery.type === "youtube") {
            Video.findInYouTube($scope.current_gallery.search_by, $scope.current_gallery.search_keyword, $scope.offset).then(function(response) {
                $scope.offset = response.nextPageToken;
                $scope.can_load_more = !itemsAlreadyLoaded(response.collection);

                if(response.collection && $scope.can_load_more) {
                    $scope.collection = $scope.collection.concat(response.collection);
                }

                $scope.is_loading = false;
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }).finally(function() {
                $scope.is_loading = false;

            });

        } else {

            Video.find($scope.current_gallery).then(function(data) {
                $scope.can_load_more = !itemsAlreadyLoaded(data.collection);

                if(data.collection && $scope.can_load_more) {
                    $scope.collection = $scope.collection.concat(data.collection);
                }

                $scope.is_loading = false;

                $scope.$broadcast('scroll.infiniteScrollComplete');
            });

        }
        offset = 0;
    };

    $scope.loadContent();

});
;/*global
 App, device, angular
 */

/**
 * Video
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Video", function($pwaRequest, Youtube) {

    var factory = {
        value_id        : null,
        extendedOptions : {}
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

    /**
     * Pre-Fetch feature.
     */
    factory.preFetch = function() {
        factory.findAll();
    };

    factory.findAll = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Video.findAll] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if(payload !== false) {

            return $pwaRequest.resolve(payload);

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("media/mobile_gallery_video_list/findall", angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));

        }


    };

    factory.find = function(item) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Video.find] missing value_id");
        }

        return $pwaRequest.get("media/mobile_gallery_video_view/find", {
            urlParams: {
                value_id    : this.value_id,
                gallery_id  : item.id,
                offset      : item.current_offset
            }
        });
    };

    factory.findInYouTube = function(search_by, keyword, offset) {

        if(search_by === "user") {
            return Youtube.findByUser(keyword, offset);
        } else if(search_by === "channel") {
            return Youtube.findByChannel(keyword, offset);
        } else {
            return Youtube.findBySearch(keyword, offset);
        }

    };

    return factory;
});
