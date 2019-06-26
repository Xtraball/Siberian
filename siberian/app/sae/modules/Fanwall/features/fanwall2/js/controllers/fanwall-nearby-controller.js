/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
    .module("starter")
    .controller("FanwallNearbyController", function ($ionicScrollDelegate, $rootScope, $scope, $state,
                                                     $stateParams, $timeout, FanwallPost, Location) {
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
            $scope.loadContent(false);
        };

        $scope.locationIsDisabled = function () {
            return !Location.isEnabled;
        };

        $scope.loadContent = function (refresh) {
            $scope.isLoading = true;

            if ($scope.locationIsDisabled()) {
                $scope.isLoading = false;

                return false;
            }

            if (refresh === true) {
                $scope.collection = [];
                FanwallPost.collection = [];

                $timeout(function () {
                    $ionicScrollDelegate.$getByHandle("mainScroll").scrollTop();
                });
            }

            return FanwallPost
                .findAllNearby($scope.collection.length, refresh)
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

        Location
            .getLocation({timeout: 10000}, true)
            .then(function (position) {
                $scope.location.latitude = position.coords.latitude;
                $scope.location.longitude = position.coords.longitude;
            }, function () {
                $scope.location.latitude = 0;
                $scope.location.longitude = 0;
            }).then(function () {
                $scope.loadContent(true);
            });
    });