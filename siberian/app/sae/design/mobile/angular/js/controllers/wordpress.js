App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/wordpress/mobile_list/index/value_id/:value_id", {
        controller: 'WordpressListController',
        templateUrl: function(params) {
            return BASE_URL+"/wordpress/mobile_list/template/value_id/"+params.value_id;
        },
        code: "wordpress"
    }).when(BASE_URL+"/wordpress/mobile_view/index/value_id/:value_id/post_id/:post_id", {
        controller: 'WordpressViewController',
        templateUrl: function(params) {
            return BASE_URL+"/wordpress/mobile_view/template/value_id/"+params.value_id;
        },
        code: "wordpress-view"
    });

}).controller('WordpressListController', function($window, $scope, $http, $routeParams, $location, Wordpress) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.collection = new Array();
    $scope.cover = {};
    $scope.is_loading = true;
    $scope.value_id = Wordpress.value_id = $routeParams.value_id;

    $scope.factory = Wordpress;
    $scope.collection = new Array();

    $scope.loadContent = function() {
        Wordpress.findAll().success(function(data) {

            $scope.collection = data.collection;

            if(!data.cover) {
                if ($scope.collection.length) {
                    for (var i in $scope.collection) {

                        if ($scope.collection[i].is_hidden) continue;

                        if ($scope.collection[i].picture) {
                            $scope.collection[i].is_hidden = true;
                            $scope.cover = $scope.collection[i];
                        }

                        break;

                    }
                }
            } else {
                $scope.cover = data.cover;
            }

            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showItem = function(item) {
        $location.path(item.url);
    };

    if($scope.isOverview) {
        $window.showPost = function(post_id) {
            if($scope.cover.id == post_id) {
                return;
            }
            for(var i = 0; i < $scope.collection.length; i++) {
                if($scope.collection[i].id == post_id) {
                    if(!$scope.cover.id && $scope.collection[i].picture) {
                        $scope.cover = {
                            id: $scope.collection[i].id,
                            title: $scope.collection[i].title,
                            subtitle: $scope.collection[i].subtitle,
                            picture: $scope.collection[i].picture
                        };
                    } else {
                        $scope.collection[i].is_hidden = false;
                    }
                }
            }
            $scope.$apply();
        };
        $window.hidePosts = function() {
            for(var i = 0; i < $scope.collection.length; i++) {
                $scope.collection[i].is_hidden = true;
            }
            $scope.cover = {};
            $scope.$apply();
        };
        $scope.$on("$destroy", function() {
            $window.showPosts = null;
            $window.hidePosts = null;
        });
    }

    $scope.loadContent();

}).controller('WordpressViewController', function($scope, $http, $routeParams, Wordpress, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Wordpress.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Wordpress.find($routeParams.post_id).then(function(post) {
            $scope.post = post;

            if($scope.post.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.post.title,
                            "picture": $scope.post.picture ? $scope.post.picture : null,
                            "content_url": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

            $scope.page_title = post.title;
            $scope.is_loading = false;
        }, function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});