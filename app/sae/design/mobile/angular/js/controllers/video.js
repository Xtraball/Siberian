App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/media/mobile_gallery_video_list/index/value_id/:value_id", {
        controller: 'VideoListController',
        templateUrl: BASE_URL+"/media/mobile_gallery_video_list/template",
        code: "video"
    });

}).controller('VideoListController', function($window, $scope, $routeParams, Sidebar, Url, Video, Youtube) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.enable_load_onscroll = true;
    $scope.sidebar = new Sidebar("video");
    $scope.value_id = Video.value_id = $routeParams.value_id;
    $scope.template_view = Url.get("/media/mobile_gallery_video_view/template");

    $scope.offset = null;

    $scope.collection = new Array();
    $scope.factory = Video;

    $scope.loadContent = function() {

        if($scope.is_loading) return;

        $scope.is_loading = true;

        Video.findAll().success(function(data) {

            Youtube.key = data.youtube_key;
            $scope.sidebar.reset();

            $scope.header_right_button = {
                action: function() {
                    if(!$scope.sidebar.current_item) return;
                    $scope.sidebar.show = !$scope.sidebar.show
                },
                picto_url: data.header_right_button.picto_url,
                hide_arrow: true
            };

            $scope.sidebar.collection = data.collection;

            $scope.page_title = data.page_title;
            $scope.sidebar.showFirstItem(data.collection)
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.sidebar.showItem = function(item) {

        if($scope.sidebar.current_item == item) return;
        $scope.sidebar.current_item = null;
        $scope.loadItem(item, 1);
        $scope.offset = null;

    };

    $scope.loadItem = function(item, offset) {

        $scope.sidebar.is_loading = true;

        item.current_offset = offset;
        $scope.sidebar.show = false;

        if(item.type == "youtube") {

            return Video.findInYouTube(item.search_by, item.search_keyword, $scope.offset).then(function(response) {

                var data = false;

                $scope.offset = response.nextPageToken;

                if(!$scope.sidebar.current_item) {
                    $scope.sidebar.current_item = item;
                    $scope.collection = response.collection;
                } else {
                    for(var i = 0; i < response.collection.length; i++) {
                        $scope.collection.push(response.collection[i]);
                    }
                }

                data = { data: response };

                if(!response.nextPageToken) {
                    data = false;
                }

                $scope.show_loader_more = false;
                $scope.sidebar.is_loading = false;

                return data;

            }).finally(function() {
                $scope.is_loading = false;
            });

        } else {

            return Video.find(item).success(function(data) {

                if (!$scope.sidebar.current_item) {
                    $scope.sidebar.current_item = item;
                    $scope.collection = data.collection;
                } else {

                    if(!$scope.collection.length) {
                        $scope.collection = data.collection;
                    } else {
                        for (var i = 0; i < data.collection.length; i++) {
                            $scope.collection.push(data.collection[i]);
                        }
                    }

                }

            }).error(function() {

            }).finally(function() {
                $scope.is_loading = false;
                $scope.sidebar.is_loading = false;
                $scope.show_loader_more = false; 
            });

        }
    };

    $scope.loadMore = function(offset) {
        if(!offset) {
            offset = 1;
        }
        
        if(!$scope.show_loader_more) {
            $scope.show_loader_more = true;
            return $scope.loadItem($scope.sidebar.current_item, offset);
        }

        return false;
    };

    $scope.loadContent();

});