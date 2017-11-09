App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/rss/mobile_feed_list/index/value_id/:value_id", {
        controller: 'RssListController',
        templateUrl: function(params) {
            return BASE_URL+"/rss/mobile_feed_list/template/value_id/"+params.value_id;
        },
        code: "rss_feed"
    }).when(BASE_URL+"/rss/mobile_feed_view/index/value_id/:value_id/feed_id/:feed_id", {
        controller: 'RssViewController',
        templateUrl: function(params) {
            return BASE_URL+"/rss/mobile_feed_view/template/value_id/"+params.value_id;
        },
        code: "rss_feed"
    });

}).controller('RssListController', function($scope, $http, $routeParams, $location, Rss) {

    $scope.is_loading = true;
    $scope.value_id = Rss.value_id = $routeParams.value_id;

    Rss.findAll().success(function(data) {
        $scope.collection = data.collection;
        $scope.cover = data.cover;
        $scope.page_title = data.page_title;
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

}).controller('RssViewController', function($scope, $http, $routeParams, Rss, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = Rss.value_id = $routeParams.value_id;
    Rss.feed_id = $routeParams.feed_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Rss.find($routeParams.feed_id).success(function(feed) {
            $scope.feed = feed;

            if($scope.feed.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.feed.title,
                            "picture": $scope.feed.image_url ? $scope.feed.image_url : null,
                            "content_url": $scope.feed.url
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});