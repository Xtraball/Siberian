App.config(function($stateProvider) {

    $stateProvider.state('facebook-list', {
        url: BASE_PATH+"/social/mobile_facebook_list/index/value_id/:value_id",
        controller: 'FacebookListController',
        templateUrl: "templates/html/l1/list.html"
    }).state('facebook-view', {
        url: BASE_PATH+"/social/mobile_facebook_view/index/value_id/:value_id/post_id/:post_id",
        controller: 'FacebookViewController',
        templateUrl: "templates/facebook/l1/view.html"
    });

}).controller('FacebookListController', function($filter, $location, $q, $scope, $state, $stateParams, $timeout, $window, Facebook, Url) {

    $scope.is_loading = true;
    $scope.can_load_older_posts = true;
    $scope.value_id = Facebook.value_id = $stateParams.value_id;
    $scope.show_posts_loader = false;
    $scope.user = {
        talking_about_count: 0,
        likes: 0,
        fan_count: 0
    };

    $scope.collection = new Array();

    $scope.template_header = "templates/facebook/l1/header.html";

    Facebook.page_urls = new Array();
    Facebook.loadData().success(function(response) {
        $scope.findUser();
        $scope.page_title = response.page_title;
    });

    $scope.findUser = function(username) {

        $scope.show_user_loader = true;

        Facebook.findUser().success(function(user) {

            user.picture = Facebook.host_img+user.id+"/picture?width=400";

            $scope.cover_image_style = {};
            if(user.cover) {
                $scope.cover_image_url = user.cover.source;$scope.show_user_loader = false;
                $scope.cover_image_style = {'background-image':'url(' + $scope.cover_image_url + ')'};
            }

            user.author = user.name;
            delete user.name;

            $scope.show_user_loader = false;
            $scope.user = user;

            $scope.show_posts_loader = true;

            $scope.loadMore();

        }, function(error) {
            $scope.show_user_loader = false;
        });
    };

    $scope.loadMore = function() {

        $scope.is_loading = true;
        $scope.show_user_loader = true;

        var deferred = $q.defer();

        Facebook.findPosts().success(function(response) {

            var posts = angular.isDefined(response.posts) ? response.posts : response;
            var new_collection = new Array();
            Facebook.page_urls['posts'] = posts.paging ? posts.paging.next : null;

            if(posts.data.length) {

                for(var i in posts.data) {

                    if(!posts.data[i].type || !posts.data[i].message) continue;

                    var post = posts.data[i];

                    var number_of_likes = !angular.isDefined(post.likes) ? 0 : post.likes.data.length >= 25 ? "> 25" : post.likes.data.length;
                    delete post.likes;

                    var number_of_comments = !angular.isDefined(post.comments) ? 0 : post.comments.data.length >= 25 ? "> 25" : post.comments.data.length;
                    delete post.comments;

                    post.subtitle = post.message;
                    delete post.message;
                    post.title = post.from.name;
                    delete post.from.name;

                    /** Better picture */
                    var picture = post.full_picture;
                    if(post.type ==  "photo") {
                        picture = Facebook.host_img+post.object_id+"/picture?width=480";
                    }

                    post.picture = picture;
                    post.details = {
                        date: {
                            text: $filter('date')(post.created_time, "short")
                        },
                        comments: {
                            text: number_of_comments
                        },
                        likes: {
                            text: number_of_likes
                        }
                    };

                    if($scope.collection.length) {
                        $scope.collection.push(post);
                    }

                    new_collection.push(post);

                }

                if(!$scope.collection.length) {
                    if(new_collection.length) {
                        $timeout(function () {
                            $scope.collection = new_collection;
                        });
                    } else {
                        $scope.can_load_older_posts = false;
                    }
                }

            } else {
                $scope.can_load_older_posts = false;
            }

            // sb-load-more directive callback formatted data
            var response = { data: { collection: new_collection } };

            deferred.resolve(response);

            $scope.is_loading = false;
            $scope.show_posts_loader = false;
            $scope.show_user_loader = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');


        }, function(error) {
            $scope.is_loading = false;
            $scope.show_posts_loader = false;
            $scope.show_user_loader = false;
            deferred.reject(error);
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });

        return deferred.promise;

    };

    $scope.showItem = function(item) {
        $state.go("facebook-view", {value_id: $scope.value_id, post_id: item.id});
    };

    $scope.removeScrollEvent = function() {
        angular.element($window).unbind('scroll');
    };

}).controller('FacebookViewController', function($filter, $rootScope, $scope, $stateParams, $translate, Dialog, Facebook) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.comments = new Array();
    $scope.show_form = false;
    $scope.value_id = Facebook.value_id = $stateParams.value_id;
    Facebook.post_id = $stateParams.post_id;
    var post = {
        number_of_comments: 0,
        number_of_likes: 0
    };

    var cache = Facebook.cache;

    $scope.showError = function(data) {

        if(data && angular.isDefined(data.message)) {
            Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
        }

        $scope.is_loading = false;
    };

    $scope.loadContent = function() {

        $scope.is_loading = true;

        if(cache.get("facebook-"+$stateParams.post_id) && !$rootScope.isOverview) {
            $scope.is_loading = false;
            $scope.post = cache.get("facebook-"+$stateParams.post_id);
        } else {
            Facebook.findPost($stateParams.post_id).success(function(_post) {

                if(angular.isDefined(_post.comments)) {

                    _post.number_of_comments = _post.comments.data.length >= 25 ? "> 25" : _post.comments.data.length;
                    Facebook.page_urls['comments'] = _post.comments.paging.next;

                    for(var i in _post.comments.data) {
                        var comment = _post.comments.data[i];
                        comment.name = comment.from.name;
                        comment.picture = Facebook.host_img+comment.from.id+"/picture?width=150";
                        comment.created_at = comment.created_time;
                        delete comment.created_time;
                        delete comment.from;
                        $scope.comments.push(comment);
                    }

                    delete _post.comments;
                } else {
                    _post.number_of_comments = 0;
                }
                if(angular.isDefined(_post.likes)) {
                    _post.number_of_likes = _post.likes.data.length >= 25 ? "> 25" : _post.likes.data.length;
                    delete _post.likes;
                } else {
                    _post.number_of_likes = 0;
                }

                var picture = _post.full_picture;
                if(_post.type ==  "photo") {
                    picture = Facebook.host_img+_post.object_id+"/picture?width=480";
                }
                _post.picture = picture;
                _post.created_at = _post.created_time;
                _post.title = _post.name;
                _post.author = _post.from.name;
                _post.icon = Facebook.host_img+_post.from.id+"/picture?width=150";
                delete _post.full_picture;
                delete _post.created_time;
                delete _post.name;
                delete _post.from;
                
                _post.message = $filter("linky")(_post.message);
                _post.description = $filter("linky")(_post.description);

                $scope.is_loading = false;
                $scope.page_title = _post.title;
                $scope.post = _post;

                cache.put("facebook-"+$stateParams.post_id, _post);

            }, $scope.showError);
        }

    };


    $scope.loadContent();

});