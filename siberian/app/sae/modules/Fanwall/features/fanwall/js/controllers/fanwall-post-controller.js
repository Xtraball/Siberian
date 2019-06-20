/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallPostController", function ($filter, $ionicScrollDelegate, $rootScope, $scope, $state,
                                               $stateParams, $timeout, $translate, Customer, Dialog, Loader, Location, Modal,
                                               Fanwall, FanwallPost, FanwallUtils) {
    angular.extend($scope, {
        isLoading: false,
        collection: [],
        hasMore: false,
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.getCardDesign = function () {
        return Fanwall.cardDesign;
    };

    $scope.getSettings = function () {
        return Fanwall.settings;
    };

    $scope.imagePath = function (image) {
        if (image.length <= 0) {
            return "./features/fanwall/assets/templates/images/placeholder.png"
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.authorImagePath = function (image) {
        if (image.length <= 0) {
            return "./features/fanwall/assets/templates/images/customer-placeholder.png"
        }
        return IMAGE_URL + "images/customer" + image;
    };

    $scope.liked = function (item) {
        return item.likes;
    };

    $scope.authorName = function (author) {
        return author.firstname + " " + author.lastname;
    };

    $scope.publicationDate = function (item) {
        return $filter("moment_calendar")(item.date * 1000);
    };

    $scope.flagPost = function (item) {
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
            .reportPost(item.id, value)
            .then(function (payload) {
                Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
            }, function (payload) {
                Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
            }).then(function () {
                Loader.hide();
            });
        });
    };

    $scope.commentModal = function (item) {
        FanwallUtils.commentModal(item);
    };

    $scope.toggleLike = function (item) {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }

        // Prevent spamming like/unlike!
        if (item.likeLocked === true) {
            return;
        }

        item.likeLocked = true;
        if (item.iLiked) {
            // Instant feedback while saving value!
            item.iLiked = false;

            FanwallPost
            .unlike(item.id)
            .then(function (payload) {
                // Decrease like count if success!
                item.likeCount--;
            }, function (payload) {
                // Revert value if failed!
                item.iLiked = true;
            }).then(function () {
                item.likeLocked = false;
            });

        } else {
            // Instant feedback while saving value!
            item.iLiked = true;

            FanwallPost
            .like(item.id)
            .then(function (payload) {
                // Increase like count if success!
                item.likeCount++;
            }, function (payload) {
                // Revert value if failed!
                item.iLiked = false;
            }).then(function () {
                item.likeLocked = false;
            });
        }
    };

    $scope.loadMore = function () {
        $scope.loadContent(false);
    };

    $scope.loadContent = function (refresh) {
        $scope.isLoading = true;

        if (refresh === true) {
            $scope.collection = [];
            FanwallPost.collection = [];

            $timeout(function () {
                $ionicScrollDelegate.$getByHandle("mainScroll").scrollTop();
            });
        }

        FanwallPost
        .findAll($scope.collection.length, refresh)
        .then(function (payload) {
            $scope.collection = $scope.collection.concat(payload.collection);
            FanwallPost.collection = FanwallPost.collection.concat(payload.collection);

            $scope.pageTitle = payload.pageTitle;

            $scope.hasMore = $scope.collection.length < payload.total;

        }, function (payload) {

        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $rootScope.$on("fanwall.refresh", function () {
        $scope.loadContent(true);
    });

    $scope.loadContent(true);
});