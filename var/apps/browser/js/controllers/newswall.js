App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('newswall-list', {
        url: BASE_PATH+'/comment/mobile_list/index/value_id/:value_id',
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l2"; break;
                case "3": layout_id = "l5"; break;
                case "4": layout_id = "l6"; break;
                case "1":
                default: layout_id = "l1";
            }
            return 'templates/html/'+layout_id+'/list.html';
        },
        controller: 'NewswallListController',
        cache: false
    }).state('newswall-view', {
        url: BASE_PATH+'/comment/mobile_view/index/value_id/:value_id/comment_id/:comment_id',
        templateUrl: 'templates/html/l1/view.html',
        controller: 'NewswallViewController'
    }).state('newswall-comment', {
        url: BASE_PATH+'/comment/mobile_comment/index/value_id/:value_id/comment_id/:comment_id',
        templateUrl: 'templates/html/l1/comment.html',
        controller: 'NewswallCommentController'
    }).state('fanwall-gallery', {
        url: BASE_PATH+'/comment/mobile_gallery/index/value_id/:value_id',
        templateUrl: 'templates/fanwall/l1/gallery.html',
        controller: 'NewswallGalleryController'
    }).state('fanwall-map', {
        url: BASE_PATH+"/comment/mobile_map/index/value_id/:value_id",
        templateUrl: "templates/html/l1/maps.html",
        controller: 'NewswallMapController'
    }).state('fanwall-edit', {
        url: BASE_PATH+"/comment/mobile_edit/value_id/:value_id",
        templateUrl: 'templates/fanwall/l1/edit.html',
        controller: 'NewswallEditController'
    });

}).controller('NewswallListController', function($cordovaGeolocation, $filter, $sbhttp, $ionicModal, $ionicScrollDelegate, $rootScope, $state, $stateParams, $scope, $translate, Customer, News, AUTH_EVENTS) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.value_id = News.value_id = $stateParams.value_id;
    $scope.collection = new Array();
    $scope.nearMeCollection = new Array();
    $scope.recentCollection = new Array();
    $scope.can_load_older_posts = true;
    $scope.can_post = false;
    $scope.showRecent = true;

    $scope.picture_fallback = "img/placeholder/530.272.png";

    $scope.loadContent = function() {

        News.findAll($scope.collection.length).success(function (data) {

            $scope.can_post = (data.code === 'fanwall');

            if ($scope.can_post && !$scope.tooltip) {

                var collection = [
                    {id: 1, "name": "Recent", "value": true},
                    {id: 2, "name": "Near me", "value": false}
                ];

                $scope.tooltip = {
                    collection: collection,
                    current_item: collection[0],
                    button_label: collection[0].name,
                    onItemClicked: function(item) {
                        $scope.showTooltip(item);
                    }
                };

                $scope.template_header = "templates/fanwall/l1/header.html";

            }

            if (data.page_title) {
                $scope.page_title = data.page_title;
            }

            $scope.can_load_older_posts = !!data.collection.length;

            $scope.collection = $scope.collection.concat(data.collection);
            $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
            $scope.recentCollection = $scope.collection;

            $rootScope.$broadcast("refreshPageSize");

        }).error(function () {

        }).finally(function () {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

    $scope.showItem = function(item) {
        if(item) {
            $state.go("newswall-view", {value_id: $scope.value_id, comment_id: item.id});
        }
    };

    $scope.loadMore = function() {
        if($scope.showRecent) {
            $scope.loadContent();
        } else {
            $scope.getNearComments();
        }
    };

    $scope.getNearComments = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {

            if($scope.showRecent) return;

            News.findNear($scope.collection.length, position.coords).success(function(data) {

                $scope.can_load_older_posts = !!data.collection.length;

                if(data.collection.length) {
                    $scope.collection = $scope.collection.concat(data.collection);
                    $scope.nearMeCollection = $scope.collection;
                    $rootScope.$broadcast("refreshPageSize");
                }

            }).error(function() {

            }).finally(function() {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                $scope.is_loading = false;
            });

        }, function (err) {

        });
    };

    $scope.showTooltip = function(show) {

        $scope.showRecent = show.value;
        $scope.can_load_older_posts = true;

        if (show.value) {
            $scope.tooltip.current_item = $scope.tooltip.collection[0];
            $scope.tooltip.button_label = $scope.tooltip.current_item.name;
            $scope.collection = $scope.recentCollection;
        }
        else {
            $scope.tooltip.current_item = $scope.tooltip.collection[1];
            $scope.tooltip.button_label = $scope.tooltip.current_item.name;
            if (!$scope.nearMeCollection.length) {
                $scope.collection = new Array();
                $scope.is_loading = true;
                $scope.getNearComments();
            } else {
                $scope.collection = $scope.nearMeCollection;
            }
        }

        $ionicScrollDelegate.resize();

    };

    $scope.goToMap = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        $state.go("fanwall-map", {value_id: $scope.value_id});
    };

    $scope.goToPhotos = function() {
        $state.go("fanwall-gallery", {value_id: $scope.value_id});
    };

    $scope.goToPost = function() {
        if(!$scope.is_logged_in) {

            $scope.$on(AUTH_EVENTS.loginSuccess, function() {
                $scope.is_logged_in = true;
                $scope.goToPost();
            });

            $ionicModal.fromTemplateUrl('templates/customer/account/l1/login.html', {
                scope: $scope,
                animation: 'slide-in-up'
            }).then(function(modal) {
                Customer.modal = modal;
                Customer.modal.show();
            });
            return;
        }
        $state.go("fanwall-edit", {value_id: $scope.value_id});
    };

    $scope.toggleMenu = function() {
        $scope.show_menu = !$scope.show_menu;
    };

    $scope.loadContent();

}).controller('NewswallViewController', function($cordovaSocialSharing, $ionicModal, $sbhttp, $rootScope, $scope, $state, $stateParams, $timeout, $translate, Application, AUTH_EVENTS, Comment, Customer, Dialog, News/*, Customer, Answers, Message, Pictos*/) {

    $scope.avatars = {};
    $scope.customerAvatar = function (customer_id) {
        if (!(customer_id in $scope.avatars)) {
            var avatar = Customer.getAvatarUrl(customer_id);
            $scope.avatars[customer_id] = avatar;
        }
        return $scope.avatars[customer_id];
    }

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });
    $scope.$on(AUTH_EVENTS.logoutSuccess, function() {
        $scope.is_logged_in = false;
        $scope.loadContent();
    });

    $scope.page_title = "";
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.value_id = News.value_id = $stateParams.value_id;

    $scope.social_sharing_active = false;    
    $scope.show_comment_button = true;
    $scope.show_like_button = true;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        News.find($stateParams.comment_id).success(function(news) {

            $scope.social_sharing_active = !!(news.social_sharing_active == 1 && !Application.is_webview);

            Comment.findAll($stateParams.comment_id).success(function(data) {
                $scope.comments = data;
            });

            $scope.show_flag_button = news.code == "fanwall";

            $scope.page_title = news.title;
            $scope.item = news;

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.share = function () {

        // Fix for $cordovaSocialSharing issue that opens dialog twice
        if($scope.is_sharing) return;

        $scope.is_sharing = true;

        var app_name = Application.app_name;
        var link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;
        var subject = "";
        var file = $scope.item.picture ? $scope.item.picture : "";
        var content = $scope.item.cleaned_message;
        var message = $translate.instant("Hi. I just found: $1 in the $2 app.").replace("$1", content).replace("$2", app_name);

        $cordovaSocialSharing
            .share(message, subject, file, link) // Share via native share sheet
            .then(function (result) {
                console.log("success");
                $scope.is_sharing = false;
            }, function (err) {
                console.log(err);
                $scope.is_sharing = false;
            });
    };

    $scope.login = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        $ionicModal.fromTemplateUrl('templates/customer/account/l1/login.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            Customer.modal = modal;
            Customer.modal.show();
        });

    };

    $scope.comment = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        $state.go("newswall-comment", {value_id: $scope.value_id, comment_id: $stateParams.comment_id});
    };

    $scope.addLike = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        News.addLike($scope.item.id).success(function(data) {
            if(data.success) {
                $scope.item.number_of_likes++;

                Dialog.alert("", data.message, $translate.instant("OK"));
            }
        }).error(function(data) {
            $scope.showError(data);
        });
    };

    $scope.flagPost = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        News.flagPost($scope.item.id).success(function(data) {
            if(data.success) {
                Dialog.alert("", data.message, $translate.instant("OK"));
            }
        }).error(function(data) {
            $scope.showError(data);
        });
    };

    $scope.flagComment = function(answer_id) {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        News.flagComment(answer_id).success(function(data) {
            if(data.success) {
                Dialog.alert("", data.message, $translate.instant("OK"));
            }
        }).error(function(data) {
            $scope.showError(data);
        });
    };

    $scope.showError = function(data) {
        if(data && angular.isDefined(data.message)) {
            Dialog.alert("", data.message, $translate.instant("OK"));
        }
    };

    $scope.loadContent();

}).controller('NewswallCommentController', function($sbhttp, $ionicModal, $rootScope, $scope, $state, $stateParams, $timeout, $translate, $window, Customer, News, Comment, Dialog/*, Customer, Answers, Message, Pictos, Application*/) {

    $scope.$on("connectionStateChange", function (event, args) {
        if (args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.value_id = News.value_id = $stateParams.value_id;

    $scope.comment = {
        id: $stateParams.comment_id,
        text: ""
    };

    $scope.post = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        if(_.isObject($scope.comment) && _.isString($scope.comment.text) && $scope.comment.text.length > 0 && $scope.comment.text.length < 1024) {
            $scope.is_loading = true;

            Comment.add($scope.comment).success(function(data) {

                if(data.success) {
                    $scope.comment.text = "";

                    Dialog.alert("", data.message, $translate.instant("OK")).then(function() {
                        $window.history.back();
                    });
                }
            }).finally(function() {
                $scope.is_loading = false;
            });
        }
    };

}).controller('NewswallGalleryController', function($sbhttp, $scope, $state, $stateParams, News) {

    $scope.is_loading = true;
    $scope.value_id = News.value_id = $stateParams.value_id;

    News.findAllPhotos().success(function(data) {
        $scope.collection = data.collection;
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.goToPost = function(item) {
        $state.go("newswall-view", { value_id: $stateParams.value_id, comment_id: item.id });
    }

}).controller('NewswallMapController', function($scope, $sbhttp, $stateParams, $cordovaGeolocation, $state, News) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = News.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        News.findAllLocation().success(function(data) {

            $scope.page_title = data.page_title;
            $scope.collection = data.collection;

            var markers = new Array();

            for(var i = 0; i < $scope.collection.length; i++) {

                var post = $scope.collection[i];

                if(post.latitude && post.longitude) {

                    var marker = {
                        title: post.text,
                        link: $state.href('newswall-view', {value_id: $scope.value_id, comment_id: post.comment_id})
                    };

                    marker.latitude = post.latitude;
                    marker.longitude = post.longitude;

                    if (post.image) {
                        marker.icon = {
                            url: post.image,
                            width: 70,
                            height: 44
                        }
                    }

                    markers.push(marker);
                }
            }

            $scope.map_config = {
                markers: markers,
                bounds_to_marker: true
            };

            $scope.is_loading = false;

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

}).controller('NewswallEditController', function($cordovaCamera, $cordovaGeolocation, $sbhttp, $ionicActionSheet, $rootScope, $scope, $state, $stateParams, $timeout, $translate, Application, Dialog, News) {

    $scope.new_post = {"text": null};
    $scope.preview_src = null;
    $scope.readyToPost = false;
    $scope.can_take_pictures = !Application.is_webview;
    $scope.value_id = News.value_id = $stateParams.value_id;

    $scope.sendPost = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        if (!$scope.new_post.text) {
            Dialog.alert($translate.instant("Error"), $translate.instant("You have to enter a text message."), $translate.instant("OK"));
            return;
        }

        $scope.is_loading = true;

        News.createComment($scope.new_post.text, $scope.preview_src, $scope.position).success(function(data) {
            $state.go("newswall-list", {value_id: $scope.value_id});
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

        $scope.readyToPost = false;
    };

    $scope.takePicture = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        if(!$scope.can_take_pictures) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        var source_type = Camera.PictureSourceType.CAMERA;

        // Show the action sheet
        var hideSheet = $ionicActionSheet.show({
            buttons: [
                { text: $translate.instant("Take a picture") },
                { text: $translate.instant("Import from Library") }
            ],
            cancelText: $translate.instant("Cancel"),
            cancel: function() {
                hideSheet();
            },
            buttonClicked: function(index) {
                if(index == 0) {
                    source_type = Camera.PictureSourceType.CAMERA;
                }
                if(index == 1) {
                    source_type = Camera.PictureSourceType.PHOTOLIBRARY;
                }

                var options = {
                    quality : 90,
                    destinationType : Camera.DestinationType.DATA_URL,
                    sourceType : source_type,
                    allowEdit : false,
                    encodingType: Camera.EncodingType.JPEG,
                    targetWidth: 1200,
                    targetHeight: 1200,
                    correctOrientation: true,
                    popoverOptions: CameraPopoverOptions,
                    saveToPhotoAlbum: false
                };

                $cordovaCamera.getPicture(options).then(function(imageData) {
                    $scope.preview_src = "data:image/jpeg;base64," + imageData;
                }, function(err) {
                    // An error occured. Show a message to the user
                });

                return true;
            }
        });

    };

    $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {
        console.log("position: ", position);
        $scope.position = position.coords;
    }, function (err) {
        console.log("err: ", err);
        $scope.position = null;
    });
});
