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
        likes: 0
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

            user.picture = Facebook.host+user.id+"/picture?width=200";

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
                        picture = Facebook.host+post.object_id+"/picture?width=320";
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

}).controller('FacebookViewController', function($scope, $stateParams, $translate, Dialog, Facebook) {

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

    $scope.showError = function(data) {

        if(data && angular.isDefined(data.message)) {
            Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
        }

        $scope.is_loading = false;
    };

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Facebook.findPost($stateParams.post_id).success(function(post) {

            if(angular.isDefined(post.comments)) {

                post.number_of_comments = post.comments.data.length >= 25 ? "> 25" : post.comments.data.length;
                Facebook.page_urls['comments'] = post.comments.paging.next;

                for(var i in post.comments.data) {
                    var comment = post.comments.data[i];
                    comment.name = comment.from.name;
                    comment.picture = Facebook.host+comment.from.id+"/picture";
                    comment.created_at = comment.created_time;
                    delete comment.created_time;
                    delete comment.from;
                    $scope.comments.push(comment);
                }

                delete post.comments;
            } else {
                post.number_of_comments = 0;
            }
            if(angular.isDefined(post.likes)) {
                post.number_of_likes = post.likes.data.length >= 25 ? "> 25" : post.likes.data.length;
                delete post.likes;
            } else {
                post.number_of_likes = 0;
            }

            var picture = post.full_picture;
            if(post.type ==  "photo") {
                picture = Facebook.host+post.object_id+"/picture?width=320";
            }
            post.picture = picture;
            post.created_at = post.created_time;
            post.title = post.name;
            post.author = post.from.name;
            post.icon = Facebook.host+post.from.id+"/picture?width=74";
            delete post.full_picture;
            delete post.created_time;
            delete post.name;
            delete post.from;

            $scope.is_loading = false;
            $scope.page_title = post.title;
            $scope.post = post;

        }, $scope.showError);

    };


    $scope.loadContent();

});