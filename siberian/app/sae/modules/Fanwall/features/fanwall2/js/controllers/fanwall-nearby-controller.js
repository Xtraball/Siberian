/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
    .module("starter")
    .controller("FanwallNearbyController", function ($ionicScrollDelegate, $rootScope, $scope, $state,
                                                     $stateParams, $timeout, Fanwall, FanwallPost, Location) {
        angular.extend($scope, {
            isLoading: true,
            collection: [],
            location: {
                latitude: 0,
                longitude: 0
            },
            hasMore: false
        });

        FanwallPost.setValueId($stateParams.value_id);

        $scope.getCardDesign = function () {
            return Fanwall.cardDesign;
        };

        $scope.loadMore = function () {
            $scope.loadContent(false, true);
        };

        $scope.locationIsDisabled = function () {
            return !Location.isEnabled;
        };

        $scope.loadContent = function (refresh, loadMore) {
            if ($scope.locationIsDisabled()) {
                return false;
            }

            if (refresh === true) {
                $scope.isLoading = true;
                $scope.collection = [];

                $timeout(function () {
                    $ionicScrollDelegate.$getByHandle("mainScroll").scrollTop();
                });
            }

            return FanwallPost
                .findAllNearby($scope.location, $scope.collection.length, refresh)
                .then(function (payload) {
                    $scope.collection = $scope.collection.concat(payload.collection);

                    $rootScope.$broadcast("fanwall.pageTitle", {pageTitle: payload.pageTitle});

                    $scope.hasMore = $scope.collection.length < payload.total;

                }, function (payload) {

                }).then(function () {
                    if (loadMore === true) {
                        $scope.$broadcast("scroll.infiniteScrollComplete");
                    }

                    if (refresh === true) {
                        $scope.isLoading = false;
                    }
                });
        };

        $rootScope.$on("fanwall.refresh", function () {
            // Refresh only the "active" tab
            if ($scope.currentTab === "nearby") {
                $scope.loadContent(true);
            }
        });

        Location
            .getLocation({timeout: 10000}, true)
            .then(function (position) {
                $scope.location.latitude = position.coords.latitude;
                $scope.location.longitude = position.coords.longitude;
            }, function () {
                $scope.location.latitude = 0;
                $scope.location.longitude = 0;
            }).then(function () {
                $scope.loadContent($scope.collection.length === 0);
            });
    });