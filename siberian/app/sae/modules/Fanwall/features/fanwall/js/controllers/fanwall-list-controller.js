/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallListController", function ($filter, $ionicScrollDelegate, $pwaRequest, $rootScope, $scope, $state,
                                               $stateParams, $timeout, $translate, Customer, Dialog, Location, Modal,
                                               FanwallPost, FanwallUtils) {
    angular.extend($scope, {
        isLoading: false,
        settingsIsLoading: true,
        is_logged_in: Customer.isLoggedIn(),
        value_id: $stateParams.value_id,
        collection: [],
        pageTitle: $translate.instant("Fan Wall", "fanwall"),
        hasMore: false,
        settings: [],
        currentTab: "topics",
        cardDesign: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.showTab = function (tabName) {
        $scope.currentTab = tabName;
    };

    $scope.applyShortFilters = function (filter) {
        console.log("applyShortFilters", filter);
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

    $scope.displayIcon = function (key) {
        var icons = $scope.settings.icons;
        switch (key) {
            case "topics":
                return (icons.topics !== null) ?
                    "<img class=\"fw-icon-header icon-topics\" src=\"" + icons.topics + "\" />" :
                    "<i class=\"icon ion-sb-fw-topics\"></i>";
            case "nearby":
                return (icons.nearby !== null) ?
                    "<img class=\"fw-icon-header icon-nearby\" src=\"" + icons.nearby + "\" />" :
                    "<i class=\"icon ion-sb-fw-nearby\"></i>";
            case "map":
                return (icons.map !== null) ?
                    "<img class=\"fw-icon-header icon-map\" src=\"" + icons.map + "\" />" :
                    "<i class=\"icon ion-sb-fw-map\"></i>";
            case "gallery":
                return (icons.gallery !== null) ?
                    "<img class=\"fw-icon-header icon-gallery\" src=\"" + icons.gallery + "\" />" :
                    "<i class=\"icon ion-sb-fw-gallery\"></i>";
            case "post":
                return (icons.post !== null) ?
                    "<img class=\"fw-icon-header icon-post\" src=\"" + icons.post + "\" />" :
                    "<i class=\"icon ion-sb-fw-post\"></i>";
        }

    };

    $scope.liked = function (item) {
        return item.likes;
    };

    $scope.authorName = function (author) {
        return author.firstname + " " + author.lastname;
    };

    $scope.publicationDate = function (item) {
        return moment(item.date * 1000).calendar();
    };

    // Modal create post!
    $scope.createPost = function () {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }
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
            alert("Youlou: " + value + ", " + item.id);
        });
    };

    $scope.commentModal = function (item) {
        FanwallUtils.commentModal(item, $scope.cardDesign);
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

    $scope.refresh = function () {
        $scope.loadContent(true);
    };

    $scope.loadMore = function () {
        $scope.loadContent(false);
    };

    $scope.loadContent = function (refresh) {
        $scope.isLoading = true;

        if (refresh === true) {
            $scope.collection = [];
            FanwallPost.collection = [];
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

    FanwallPost
    .loadSettings()
    .then(function (payload) {
        $scope.settings = payload.settings;
        $scope.cardDesign = payload.settings.cardDesign;
        $scope.settingsIsLoading = false;
    });

    $scope.loadContent(true);
});