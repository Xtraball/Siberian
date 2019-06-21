/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallGalleryController", function ($scope, $state, $stateParams, $timeout, $ionicScrollDelegate,
                                                  Fanwall, FanwallGallery) {
    angular.extend($scope, {
        isLoading: false,
        collection: [],
        hasMore: false,
    });

    FanwallGallery.setValueId($stateParams.value_id);

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

    $scope.loadMore = function () {
        $scope.loadContent(false);
    };

    $scope.loadContent = function (refresh) {
        $scope.isLoading = true;

        if (refresh === true) {
            $scope.collection = [];
            FanwallGallery.collection = [];

            $timeout(function () {
                $ionicScrollDelegate.$getByHandle("mainScroll").scrollTop();
            });
        }

        FanwallGallery
        .findAll($scope.collection.length, refresh)
        .then(function (payload) {
            $scope.collection = $scope.collection.concat(payload.collection);
            FanwallGallery.collection = FanwallGallery.collection.concat(payload.collection);

            $scope.pageTitle = payload.pageTitle;

            $scope.hasMore = $scope.collection.length < payload.total;

        }, function (payload) {

        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.loadContent(true);
});