angular
.module("starter")
.directive("fanwallPostItem", function ($rootScope, $filter, $sce, $translate, $timeout, Customer, Dialog, Loader,
                                        Fanwall, FanwallPost, FanwallUtils, Lightbox, Popover) {
        return {
            restrict: 'E',
            templateUrl: "features/fanwall2/assets/templates/l1/tabs/directives/post-item.html",
            controller: function ($scope) {
                $scope.actionsPopover = null;

                $scope.getCardDesign = function () {
                    return Fanwall.cardDesign;
                };

                $scope.getSettings = function () {
                    return Fanwall.settings;
                };

                $scope.userLike = function () {
                    return $scope.getSettings().features.enableUserLike;
                };

                $scope.userComment = function () {
                    return $scope.getSettings().features.enableUserComment;
                };

                $scope.showText = function () {
                    return $filter("linky")($scope.post.text);
                };

                $scope.showLikeOrComment = function () {
                    return ($scope.post.likeCount > 0 || $scope.post.commentCount > 0) &&
                        ($scope.canLikeOrComment());
                };

                $scope.canLikeOrComment = function () {
                    return ($scope.userLike() || $scope.userComment());
                };

                $scope.imagePath = function () {
                    if ($scope.post.image.length <= 0) {
                        return "./features/fanwall2/assets/templates/images/placeholder.png";
                    }
                    return IMAGE_URL + "images/application" + $scope.post.image;
                };

                $scope.authorImagePath = function () {
                    // Empty image
                    if ($scope.post.author.image.length <= 0) {
                        return "./features/fanwall2/assets/templates/images/customer-placeholder.png";
                    }
                    // App icon
                    if ($scope.post.author.image.indexOf("/var/cache") === 0) {
                        return IMAGE_URL + $scope.post.author.image;
                    }
                    return IMAGE_URL + "images/customer" + $scope.post.author.image;
                };

                $scope.liked = function () {
                    return $scope.post.likes;
                };

                $scope.authorName = function () {
                    return $scope.post.author.firstname + " " + $scope.post.author.lastname;
                };

                $scope.publicationDate = function () {
                    return $filter("moment_calendar")($scope.post.date * 1000);
                };

                // Popover actions!
                $scope.openActions = function ($event) {
                    Popover
                    .fromTemplateUrl("features/fanwall2/assets/templates/l1/tabs/directives/actions-popover.html", {
                        scope: $scope
                    }).then (function (popover) {
                        $scope.actionsPopover = popover;
                        $scope.actionsPopover.show($event);
                    });
                };

                $scope.closeActions = function () {
                    try {
                        $scope.actionsPopover.hide();
                    } catch (e) {
                        // We skip!
                    }
                };

                $scope.flagPost = function () {
                    var title = $translate.instant("Report this message!", "fanwall");
                    var message = $translate.instant("Please let us know why you think this message is inappropriate.", "fanwall");
                    var placeholder = $translate.instant("Your message.", "fanwall");

                    Dialog
                        .prompt(
                            title,
                            message,
                            "text",
                            placeholder)
                        .then(function (value) {
                            Loader.show();

                            FanwallPost
                                .reportPost($scope.post.id, value)
                                .then(function (payload) {
                                    Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
                                }, function (payload) {
                                    Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
                                }).then(function () {
                                    Loader.hide();
                                });
                        });
                };

                $scope.blockUser = function () {
                    Dialog
                    .confirm(
                        "Confirmation",
                        "You are about to block all this user messages!",
                        ["YES", "NO"],
                        -1,
                        "fanwall")
                    .then(function (value) {
                        if (!value) {
                            return;
                        }
                        Loader.show();

                        FanwallPost
                        .blockUser($scope.post.id, value)
                        .then(function (payload) {
                            $rootScope.$broadcast("fanwall.refresh");
                            Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
                        }, function (payload) {
                            Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
                        }).then(function () {
                            Loader.hide();
                        });
                    });
                };

                $scope.deletePost = function () {
                    Dialog
                    .confirm(
                        "Confirmation",
                        "You are about to delete this post!",
                        ["YES", "NO"],
                        -1,
                        "fanwall")
                    .then(function (value) {
                        if (!value) {
                            return;
                        }
                        Loader.show();

                        FanwallPost
                        .deletePost($scope.post.id, value)
                        .then(function (payload) {
                            $rootScope.$broadcast("fanwall.refresh");
                            Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
                        }, function (payload) {
                            Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
                        }).then(function () {
                            Loader.hide();
                        });
                    });
                };

                $scope.commentModal = function () {
                    FanwallUtils.commentModal($scope.post);
                };

                $scope.toggleLike = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    // Prevent spamming like/unlike!
                    if ($scope.post.likeLocked === true) {
                        return false;
                    }

                    $scope.post.likeLocked = true;
                    if ($scope.post.iLiked) {
                        // Instant feedback while saving value!
                        $scope.post.iLiked = false;

                        FanwallPost
                            .unlike($scope.post.id)
                            .then(function (payload) {
                                // Decrease like count if success!
                                $scope.post.likeCount--;
                            }, function (payload) {
                                // Revert value if failed!
                                $scope.post.iLiked = true;
                            }).then(function () {
                                $scope.post.likeLocked = false;
                            });

                    } else {
                        // Instant feedback while saving value!
                        $scope.post.iLiked = true;

                        FanwallPost
                            .like($scope.post.id)
                            .then(function (payload) {
                                // Increase like count if success!
                                $scope.post.likeCount++;
                            }, function (payload) {
                                // Revert value if failed!
                                $scope.post.iLiked = false;
                            }).then(function () {
                                $scope.post.likeLocked = false;
                            });
                    }

                    return true;
                };

                $scope.isEnabled = function (key) {
                    var features = $scope.getSettings().features;

                    return features.key;
                };

                $scope.isOwner = function () {
                    if (!Customer.isLoggedIn()) {
                        return false;
                    }

                    return Customer.customer.id === $scope.post.customerId;
                };

                $scope.editPost = function () {
                    return FanwallUtils.postModal($scope.post);
                };

                $rootScope.$on("fanwall.modal.ready", function () {
                    $timeout(function () {
                        Lightbox.run(".show-post");
                    }, 200);
                });
            }
        };
    });


