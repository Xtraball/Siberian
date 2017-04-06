App.config(function($stateProvider) {

    $stateProvider.state('video-list', {
        url: BASE_PATH + "/media/mobile_gallery_video_list/index/value_id/:value_id",
        controller: 'VideoListController',
        templateUrl: "templates/media/video/l1/list.html"
    });

}).controller('VideoListController', function($scope, $stateParams, $timeout,$rootScope, Video, Youtube) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.enable_load_onscroll = true;
    $scope.value_id = Video.value_id = $stateParams.value_id;

    $scope.offset = null;
    $scope.collection = [];
    $scope.show_galleries = false;
    $scope.factory = Video;
    $scope.galleries = [];

    var itemsAlreadyLoaded = function(items) {
        return items.every(function(item){
            return $scope.collection.some(function(c){
                return c.video_id == item.video_id;
            });
        });
    }

    $scope.loadContent = function() {
        $scope.offset =null;
        if($scope.is_loading) return;

        $scope.is_loading = true;

        Video.findAll().success(function(data) {
            Youtube.key = data.youtube_key;
            $scope.galleries = data.collection;
            if($scope.galleries.length) {
                $scope.is_loading = false;
                $scope.showGallery($scope.galleries[0]);

            }
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;

        });

    };

    $scope.toggleGalleries = function() {
        $scope.show_galleries = !$scope.show_galleries;
    };

    $scope.showGallery = function(gallery) {

        $scope.show_galleries = false;

        if($scope.current_gallery && $scope.current_gallery.id == gallery.id){

           if($scope.is_loading) return;
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

        if($scope.current_gallery.type == "youtube") {
            Video.findInYouTube($scope.current_gallery.search_by, $scope.current_gallery.search_keyword, $scope.offset).then(function(response) {
                $scope.offset = response.nextPageToken;
                $scope.can_load_more = !itemsAlreadyLoaded(response.collection);

                if(response.collection && $scope.can_load_more) {
                    $scope.collection = $scope.collection.concat(response.collection);
                }

                $scope.is_loading = false;
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }, function(err){
                console.log(err);

            }).finally(function() {
                $scope.is_loading = false;

            });

        } else {

            Video.find($scope.current_gallery).success(function(data) {
                $scope.can_load_more = !itemsAlreadyLoaded(data.collection);

                if(data.collection && $scope.can_load_more) {
                    $scope.collection = $scope.collection.concat(data.collection);
                }

                $scope.is_loading = false;
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }).finally(function() {
                
                $scope.is_loading = false;
               

            });

        }
        offset = 0;
    };

    

    $scope.$on('$ionicView.enter', function(){

        $scope.loadContent();

    });
    
    $scope.$on('onDebug',function(event, debug){
      console.log(debug.message);
      console.log(debug.object);
    
    });

   

    

});
