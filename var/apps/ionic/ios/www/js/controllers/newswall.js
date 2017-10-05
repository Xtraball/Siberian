/* global
 App, angular, BASE_PATH, DOMAIN
 */

angular.module('starter').controller('NewswallListController', function ($filter, $ionicScrollDelegate, $pwaRequest,
                                                                        $rootScope, $scope, $state, $stateParams,
                                                                        $timeout, $translate, Customer, Location,
                                                                        Modal, Newswall) {
    angular.extend($scope, {
        is_loading: true,
        is_logged_in: Customer.isLoggedIn(),
        value_id: $stateParams.value_id,
        collection: [],
        nearMeCollection: [],
        recentCollection: [],
        load_more: true,
        can_post: false,
        use_pull_refresh: true,
        pull_to_refresh: false,
        showRecent: true,
        picture_fallback: './img/placeholder/530.272.png',
        card_design: false
    });

    Newswall.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Newswall.findAll($scope.collection.length)
            .then(function (data) {
                $scope.can_post = (data.code === 'fanwall');

                if ($scope.can_post && !$scope.tooltip) {
                    var collection = [
                        { id: 1, 'name': 'Recent', 'value': true },
                        { id: 2, 'name': 'Near me', 'value': false }
                    ];

                    $scope.tooltip = {
                        collection: collection,
                        current_item: collection[0],
                        button_label: collection[0].name,
                        onItemClicked: function (item) {
                            $scope.showTooltip(item);
                        }
                    };

                    $scope.template_header = 'templates/fanwall/l1/header.html';
                }

                if (data.page_title) {
                    $scope.page_title = data.page_title;
                }

                $scope.load_more = (data.collection.length === data.displayed_per_page);
                $scope.collection = $scope.collection.concat(data.collection);
                Newswall.collection = $scope.collection;
                $scope.collection_chunks = $filter('chunk')($scope.collection, 2);
                $scope.recentCollection = $scope.collection;

                $rootScope.$broadcast('refreshPageSize');
            }).then(function () {
                $scope.is_loading = false;
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.load_more = false;

        Newswall.findAll(0, true)
            .then(function (data) {
                $scope.can_post = (data.code === 'fanwall');

                if ($scope.can_post && !$scope.tooltip) {
                    var collection = [
                        { id: 1, 'name': 'Recent', 'value': true },
                        { id: 2, 'name': 'Near me', 'value': false }
                    ];

                    $scope.tooltip = {
                        collection: collection,
                        current_item: collection[0],
                        button_label: collection[0].name,
                        onItemClicked: function (item) {
                            $scope.showTooltip(item);
                        }
                    };

                    $scope.template_header = 'templates/fanwall/l1/header.html';
                }

                if (data.page_title) {
                    $scope.page_title = data.page_title;
                }

                $scope.load_more = (data.collection.length === data.displayed_per_page);

                $scope.collection = data.collection;
                Newswall.collection = $scope.collection;
                $scope.collection_chunks = $filter('chunk')($scope.collection, 2);
                $scope.recentCollection = $scope.collection;
            }).then(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.pull_to_refresh = false;

                $timeout(function () {
                    $scope.load_more = !!$scope.collection.length;
                }, 500);
            });
    };

    $scope.showItem = function (item) {
        if (item) {
            $state.go('newswall-view', {
                value_id: $scope.value_id,
                comment_id: item.id
            });
        }
    };

    $scope.loadMore = function () {
        if ($scope.showRecent) {
            $scope.loadContent();
        } else {
            $scope.getNearComments();
        }
    };

    $scope.getNearComments = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Location.getLocation()
            .then(function (position) {
                if ($scope.showRecent) {
                    return;
                }

                Newswall.findNear($scope.collection.length, position.coords)
                    .then(function (data) {
                        $scope.load_more = !!data.collection.length;

                        if (data.collection.length) {
                            $scope.collection = $scope.collection.concat(data.collection);
                            $scope.nearMeCollection = $scope.collection;
                            $rootScope.$broadcast('refreshPageSize');
                        }
                    }).then(function () {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                        $scope.is_loading = false;
                    });
            });
    };

    $scope.showTooltip = function (show) {
        $scope.showRecent = show.value;
        $scope.load_more = true;

        if (show.value) {
            $scope.tooltip.current_item = $scope.tooltip.collection[0];
            $scope.tooltip.button_label = $scope.tooltip.current_item.name;
            $scope.collection = $scope.recentCollection;
        } else {
            $scope.tooltip.current_item = $scope.tooltip.collection[1];
            $scope.tooltip.button_label = $scope.tooltip.current_item.name;
            if (!$scope.nearMeCollection.length) {
                $scope.collection = [];
                $scope.is_loading = true;
                $scope.getNearComments();
            } else {
                $scope.collection = $scope.nearMeCollection;
            }
        }

        $ionicScrollDelegate.resize();
    };

    $scope.goToMap = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        $state.go('fanwall-map', {
            value_id: $scope.value_id
        });
    };

    $scope.goToPhotos = function () {
        $state.go('fanwall-gallery', {
            value_id: $scope.value_id
        });
    };

    $scope.goToPost = function () {
        if (!Customer.isLoggedIn()) {
            Customer.loginModal($scope, function () {
                $scope.is_logged_in = Customer.isLoggedIn();
                $scope.goToPost();
            });
        } else {
            $state.go('fanwall-edit', {
                value_id: $scope.value_id
            });
        }
    };

    $scope.toggleMenu = function () {
        $scope.show_menu = !$scope.show_menu;
    };

    $scope.loadContent();
}).controller('NewswallViewController', function (Modal, $pwaRequest, $rootScope, $scope,
                                                 $state, $stateParams, $timeout, $translate, Application, SB,
                                                 Comment, Customer, Dialog, Newswall, SocialSharing, Loader) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        avatars: {},
        page_title: '',
        is_logged_in: Customer.isLoggedIn(),
        social_sharing_active: false,
        show_comment_button: true,
        show_like_button: true,
        card_design: true
    });

    Newswall.setValueId($stateParams.value_id);

    $scope.customerAvatar = function (customer_id) {
        if (!(customer_id in $scope.avatars)) {
            var avatar = Customer.getAvatarUrl(customer_id);
            $scope.avatars[customer_id] = avatar;
        }
        return $scope.avatars[customer_id];
    };

    $scope.loadContent = function (refresh) {
        $scope.is_logged_in = Customer.isLoggedIn();

        $scope.is_loading = true;

        Newswall.getComment($stateParams.comment_id)
            .then(function (news) {
                $scope.social_sharing_active = (news.social_sharing_active && $rootScope.isNativeApp);
                $scope.comments = news.answers;
                $scope.show_flag_button = (news.code === 'fanwall');
                $scope.page_title = news.title;
                $scope.item = news;
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.share = function () {
        var file = ($scope.item.picture) ? $scope.item.picture : undefined;

        SocialSharing.share($scope.item.cleaned_message, undefined, undefined, undefined, file);
    };

    $scope.login = function () {
        Customer.loginModal($scope, function () {
            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.loadContent(true);
        });
    };

    $scope.comment = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        $state.go('newswall-comment', {
            value_id: $scope.value_id,
            comment_id: $stateParams.comment_id
        });
    };

    $scope.addLike = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Loader.show();

        Newswall.addLike($scope.item.id)
            .then(function (data) {
                if (data.success) {
                    $scope.item.number_of_likes++;

                    Dialog.alert('', data.message, 'OK', -1);
                }
            }, function (data) {
                $scope.showError(data);
            }).then(function () {
                Loader.hide();
            });
    };

    $scope.flagPost = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Loader.show();

        Newswall.flagPost($scope.item.id)
            .then(function (data) {
                if (data.success) {
                    Dialog.alert('', data.message, 'OK', -1);
                }
            }, function (data) {
                $scope.showError(data);
            }).then(function () {
                Loader.hide();
            });
    };

    $scope.flagComment = function (answer_id) {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Loader.show();

        Newswall.flagComment(answer_id)
            .then(function (data) {
                if (data.success) {
                    Dialog.alert('', data.message, 'OK', -1);
                }
            }, function (data) {
                $scope.showError(data);
            }).then(function () {
                Loader.hide();
            });
    };

    $scope.showError = function (data) {
        if (data && angular.isDefined(data.message)) {
            Dialog.alert('Error', data.message, 'OK', -1);
        }
    };

    $scope.loadContent();

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
$scope.loadContent();
});
    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
$scope.loadContent();
});
}).controller('NewswallCommentController', function ($ionicHistory, $pwaRequest, $rootScope, $scope, $state,
                                                    $stateParams, $timeout, $translate, $window, Comment, Customer,
                                                    Dialog, Modal, Newswall) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        is_logged_in: Customer.isLoggedIn(),
        comment: {
            id: $stateParams.comment_id,
            text: ''
        },
        card_design: false
    });

    Newswall.setValueId($stateParams.value_id);

    $scope.post = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if (_.isObject($scope.comment) &&
            _.isString($scope.comment.text) &&
            $scope.comment.text.length > 0 &&
            $scope.comment.text.length < 1024) {
            $scope.is_loading = true;

            Comment.add($scope.comment)
                .then(function (data) {
                    if (data.success) {
                        $scope.comment.text = '';

                        Dialog.alert('', data.message, 'OK', -1)
                            .then(function () {
                                Newswall.findAll(0, true)
                                    .then(function () {
                                        $ionicHistory.goBack();
                                    });
                            });
                    }
                }).then(function () {
                    $scope.is_loading = false;
                });
        }
    };
}).controller('NewswallGalleryController', function ($pwaRequest, $scope, $state, $stateParams, Newswall) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        card_design: false
    });

    Newswall.setValueId($stateParams.value_id);

    Newswall.findAllPhotos()
        .then(function (data) {
            $scope.collection = data.collection;
        }).then(function () {
            $scope.is_loading = false;
        });

    $scope.goToPost = function (item) {
        $state.go('newswall-view', {
            value_id: $stateParams.value_id,
            comment_id: item.id
        });
    };
}).controller('NewswallMapController', function ($scope, $pwaRequest, $stateParams, Location, $state, Newswall) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        card_design: false
    });

    Newswall.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Newswall.findAllLocation()
            .then(function (data) {
                $scope.page_title = data.page_title;
                $scope.collection = data.collection;

                var markers = [];

                for (var i = 0; i < $scope.collection.length; i++) {
                    var post = $scope.collection[i];

                    if (post.latitude && post.longitude) {
                        var marker = {
                            title: post.text,
                            link: $state.href('newswall-view', {
                                value_id: $scope.value_id,
                                comment_id: post.comment_id
                            })
                        };

                        marker.latitude = post.latitude;
                        marker.longitude = post.longitude;

                        if (post.image) {
                            marker.icon = {
                                url: post.image,
                                width: 70,
                                height: 44
                            };
                        }

                        markers.push(marker);
                    }
                }

                $scope.map_config = {
                    markers: markers,
                    bounds_to_marker: true
                };

                $scope.is_loading = false;
            }, function () {
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();
}).controller('NewswallEditController', function (Location, $pwaRequest, $ionicActionSheet,
                                                 $rootScope, $scope, $state, $stateParams, $timeout, $translate,
                                                 Application, Dialog, Newswall, Picture, Loader) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        new_post: {
            'text': null
        },
        preview_src: null,
        readyToPost: false,
        card_design: false
    });

    Newswall.setValueId($stateParams.value_id);

    $scope.sendPost = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if (!$scope.new_post.text) {
            Dialog.alert('Error', 'You have to enter a text message.', 'OK', -1);
            return;
        }

        Loader.show();

        Newswall.createComment($scope.new_post.text, $scope.preview_src, $scope.position)
            .then(function (data) {
                Newswall.findAll(0, true);

                $state.go('newswall-list', {
                    value_id: $scope.value_id
                });
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK', -1)
                    .then(function () {
                        return true;
                    });
            }).then(function () {
                Loader.hide();
            });

        $scope.readyToPost = false;
    };

    /**
     * Error message is handled by the Picture service.
     */
    $scope.takePicture = function () {
        Picture.takePicture()
            .then(function (response) {
                $scope.preview_src = response.image;
            });
    };

    Location.getLocation()
        .then(function (position) {
            $scope.position = position.coords;
        }, function (error) {
            $scope.position = null;
        });
});
