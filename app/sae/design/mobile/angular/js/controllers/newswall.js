App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/comment/mobile_list/index/value_id/:value_id", {
        controller: 'NewswallListController',
        templateUrl: function(params) {
            return BASE_URL+"/comment/mobile_list/template/value_id/"+params.value_id;
        },
        code: "newswall"
    }).when(BASE_URL+"/comment/mobile_view/index/value_id/:value_id/comment_id/:comment_id", {
        controller: 'NewswallViewController',
        templateUrl: function(params) {
            return BASE_URL+"/comment/mobile_view/template/value_id/"+params.value_id;
        },
        code: "newswall"
    }).when(BASE_URL+"/comment/mobile_gallery/index/value_id/:value_id", {
        controller: 'NewswallGalleryController',
        templateUrl: BASE_URL+"/comment/mobile_gallery/template",
        code: "newswall"
    }).when(BASE_URL+"/comment/mobile_map/index/value_id/:value_id", {
        controller: 'NewswallMapController',
        templateUrl: BASE_URL+"/comment/mobile_map/template",
        code: "newswall"
    }).when(BASE_URL+"/comment/mobile_edit/value_id/:value_id", {
        controller: 'NewswallEditController',
        templateUrl: BASE_URL+"/comment/mobile_edit/template",
        code: "newswall"
    });

}).controller('NewswallListController', function($scope, $http, $routeParams, $window, $rootScope, $location, Application, Customer, News, Url) {

    $scope.is_loading = true;
    $scope.value_id = News.value_id = $routeParams.value_id;
    $scope.factory = News;
    $scope.collection = new Array();

    News.findAll().success(function(data) {
        $scope.recentCollection = data.collection;
        $scope.page_title = data.page_title;
        $scope.can_post = (data.code === 'fanwall');

        if ($scope.can_post) {
            if (Customer.isLoggedIn()) {
                rightAction = $scope.addNews;
            } else {
                rightAction = $scope.goToLogin;
            }

            $scope.header_right_button = {
                action: rightAction,
                hide_arrow: true,
                picto_url: data.header_right_button.picto_url
            };
        }

        $scope.setShowRecent(true);

        $rootScope.$broadcast("refreshPageSize");

    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    // Cached filtered collection
    $scope.nearMeCollection = null;

    $scope.getNearComments = function() {

        $scope.is_loading = true;
        Application.getLocation(function(position) {

            if($scope.showRecent) return;

            News.findNear(position).success(function(data) {
                $scope.nearMeCollection = data.collection;
                $scope.collection = $scope.nearMeCollection;
            }).error(function() {

            }).finally(function() {
                $scope.is_loading = false;
            });

        }, function (err) {
            $scope.is_loading = false;
        });
    };

    $scope.setShowRecent = function(show) {
        $scope.showRecent = show;
        if (show) {
            $scope.collection = $scope.recentCollection;
            $scope.is_loading = false;
        }
        else {
            if ($scope.nearMeCollection === null) {
                $scope.collection = [];
                $scope.getNearComments();
            } else {
                $scope.collection = $scope.nearMeCollection;
            }
        }
    };

    $scope.showItem = function(item) {
        $location.path(item.url);
    };

    $scope.addNews = function () {
        if(!$scope.is_loading) {
            $scope.is_loading = true;
            $location.path(Url.get("comment/mobile_edit", {
                value_id: $routeParams.value_id
            }));
        }
    };

    $scope.goToLogin = function () {
        if(!$scope.is_loading) {
            $location.path(Url.get("customer/mobile_account_login"));
        }
    };

    $scope.getMapUrl = function() {
        return Url.get("comment/mobile_map/index", {
            value_id: $routeParams.value_id
        });
    };

    $scope.getPhotosUrl = function() {
        return Url.get("comment/mobile_gallery/index", {
            value_id: $routeParams.value_id
        });
    };

}).controller('NewswallViewController', function($scope, $http, $routeParams, Customer, News, Answers, Message, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.show_form = false;
    $scope.value_id = News.value_id = Answers.value_id = $routeParams.value_id;
    Answers.comment_id = $routeParams.comment_id;

    $scope.showError = function(data) {

        if(data && angular.isDefined(data.message)) {
            $scope.message = new Message();
            $scope.message.isError(true)
            .setText(data.message)
            .show()
            ;
        }
    };

    $scope.loadContent = function() {

        $scope.is_loading = true;

        News.find($routeParams.comment_id).success(function(news) {

            $scope.post = news;

            if($scope.post.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.page_title,
                            "picture": $scope.post.picture ? $scope.post.picture : null,
                            "content_url": null,
                            "content": $scope.post.cleaned_message
                        };
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

            $scope.page_title = news.author;
            $scope.canPost = news.code == 'fanwall';

            Answers.findAll($routeParams.comment_id).success(function(comments) {
                $scope.comments = comments;
                $scope.post.number_of_comments = comments.length;

            }).error($scope.showError).finally(function() {
                $scope.is_loading = false;
            });

        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.showForm = function() {
        $scope.show_form = true;
    }

    $scope.addAnswer = function() {
        Answers.add($scope.post.new_answer).success(function(data) {
            $scope.message = new Message();
            $scope.message.setText(data.message)
            .isError(false)
            .show()
            ;
            $scope.comments.push(data.answer);
            $scope.post.number_of_comments = $scope.comments.length;
            $scope.show_form = false;
            $scope.new_answer = "";
        }).error(this.showError)
        .finally(ajaxComplete);
    }

    $scope.addLike = function() {
        News.addLike($scope.post.id).success(function(data) {
            if(data.success) {
                $scope.post.number_of_likes++;
                $scope.message = new Message();
                $scope.message.setText(data.message)
                .isError(false)
                .show()
                ;
            }
        }).error($scope.showError)
        .finally(ajaxComplete);
    }

    $scope.flagPost = function() {
        News.flagPost($scope.post.id).success(function(data) {
            if(data.success) {
                $scope.message = new Message();
                $scope.message.setText(data.message)
                .isError(false)
                .show()
                ;
            }
        }).error($scope.showError)
        .finally(ajaxComplete);
    }

    $scope.flagComment = function(answer_id) {
        News.flagComment(answer_id).success(function(data) {
            if(data.success) {
                $scope.message = new Message();
                $scope.message.setText(data.message)
                .isError(false)
                .show()
                ;
            }
        }).error($scope.showError)
        .finally(ajaxComplete);
    }

    $scope.loadContent();

}).controller('NewswallGalleryController', function($scope, $http, $routeParams, $location, News) {

    $scope.is_loading = true;
    $scope.value_id = News.value_id = $routeParams.value_id;

    News.findAllPhotos().success(function(data) {
        $scope.collection = data.collection;
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.showItem = function(link) {
        $location.path(link);
    };

}).controller('NewswallMapController', function($scope, $http, $routeParams, $location, $q, News, Message, Url, GoogleMapService, MathsMapService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = News.value_id = $routeParams.value_id;
    $scope.message = new Message();

    $scope.loadContent = function () {

        News.findAllLocation().success(function(data) {
            var markersPromises = data.collection.reduce(function (markersPromises, place) {

                // build marker
                var marker = {
                    title: place.text,
                    icon: place.image,
                    link: place.link,
                    latitude: place.latitude,
                    longitude: place.longitude
                };

                markersPromises.push(marker);
                return markersPromises;

            }, []);

            $q.all(markersPromises).then(function (markers) {

                if (markers.length === 0) {
                    $scope.message.setText('No place to display on map.')
                    .isError(true)
                    .show();
                    $scope.is_loading = false;
                } else {
                    var bounds = MathsMapService.getBoundsFromPoints(markers);

                    $scope.mapConfig = {
                        center: {
                            bounds: bounds
                        },
                        markers: markers
                    };
                    
                    $scope.is_loading = false;
                }

            }, function () {

                $scope.message.setText('An error occurred while loading places.')
                .isError(true)
                .show();
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

}).controller('NewswallEditController', function($scope, $http, $routeParams, $location, $q, $timeout, $window, Application, News, Url) {

    $scope.text = "";
    $scope.readyToPost = false;
    $scope.handle_camera_picture = Application.handle_camera_picture;

    $scope.header_right_button = {
        action: function() {
            $scope.createPost();
        },
        hide_arrow: true,
        title: "OK"
    };

    $scope.sendPost = function() {

        if (!$scope.readyToPost) return;

        $scope.is_loading = true;

        News.createComment($scope.text, $scope.image, $scope.position).success(function(data) {
            $window.history.back();
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

        $scope.readyToPost = false;
    };

    $scope.createPost = function() {

        $scope.value_id = News.value_id = $routeParams.value_id;

        $scope.readyToPost = true;
        $scope.sendPost();
    };

    $scope.imageSelected = function(element) {

        if(element.files.length > 0) {
            var file = element.files[0];

            var reader = new FileReader();
            var img = document.getElementById('image');
            reader.onload = (function(aImg) {
                return function(e) {
                    var content = e.target.result;
                    $timeout(function() {
                        aImg.src = content;
                        $scope.image = content;
                        $scope.preview_src = content;
                    });
                };
            }) (img);

            reader.readAsDataURL(file);

        } else {
            // Only needed on Chrome when pressing Cancel
            $scope.product.imageContent = undefined;
        }
    };

    $scope.openCamera = function() {
        Application.openCamera(function(image_url) {
            image_url = "data:image/jpg;base64,"+image_url;
            $timeout(function() {
                $scope.preview_src = image_url;
                $scope.image = image_url;
            });
        }, function() {

        });
    };

    Application.getLocation(function(position) {
        $scope.position = position;
        $scope.sendPost();
    }, function (err) {
        $scope.position = null;
        $scope.sendPost();
    });
});
