/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("FanwallPostController", function ($ionicScrollDelegate, $rootScope, $scope, $state,
                                               $stateParams, $timeout, FanwallPost) {
    angular.extend($scope, {
        isLoading: false,
        collection: [],
        hasMore: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.loadMore = function () {
        $scope.loadContent(false);
    };

    $scope.loadContent = function (refresh) {
        if (refresh === true) {
            $scope.isLoading = true;
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

            $rootScope.$broadcast("fanwall.pageTitle", {pageTitle: payload.pageTitle});

            $scope.hasMore = $scope.collection.length < payload.total;

        }, function (payload) {

        }).then(function () {
            if (refresh === true) {
                $scope.isLoading = false;
            }
        });
    };


    $rootScope.$on("fanwall.refresh", function () {
        // Refresh only the "active" tab
        if ($scope.currentTab === "post") {
            $scope.loadContent(true);
        }
    });

    $scope.loadContent($scope.collection.length === 0);
});