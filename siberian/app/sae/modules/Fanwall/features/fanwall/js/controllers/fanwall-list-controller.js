/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallListController", function ($filter, $ionicScrollDelegate, $pwaRequest, $rootScope, $scope, $state,
                                               $stateParams, $timeout, $translate, Customer, Location, Modal, FanwallPost) {
    angular.extend($scope, {
        isLoading: false,
        is_logged_in: Customer.isLoggedIn(),
        value_id: $stateParams.value_id,
        collection: [],
        shortFilters: [
            { 
                id: 1, 
                name: $translate.instant("Recent", "fanwall"),
                value: true
            },
            { 
                id: 2, 
                name: $translate.instant("Near me", "fanwall"),
                value: false
            }
        ],
        pageTitle: $translate.instant("Fan Wall", "fanwall"),
        hasMore: false,
        currentTab: "posts",
        cardDesign: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.showTab = function (tabName) {
        $scope.currentTab = tabName;
    };

    $scope.refresh = function () {
        $scope.loadContent(true);
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

    $scope.liked = function (item) {
        return item.likes
    };

    $scope.authorName = function (author) {
        return author.firstname + " " + author.lastname;
    };

    $scope.publicationDate = function (item) {
        return moment(item.date).calendar();
    };

    // Modal create post!
    $scope.createPost = function () {

    };

    $scope.toggleLike = function (item) {
        if (item.iLiked) {
            //FanwallPost.unlike(item.id);
            item.iLiked = false;
        } else {
            //FanwallPost.like(item.id);
            item.iLiked = true;
        }
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
            $scope.collection = payload.collection;
            FanwallPost.collection = payload.collection;

            $scope.pageTitle = payload.pageTitle;

            $scope.hasMore = $scope.collection.length < payload.total;
        }, function (payload) {

        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.loadContent();
});