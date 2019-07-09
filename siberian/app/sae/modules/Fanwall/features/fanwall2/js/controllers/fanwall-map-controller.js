/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("FanwallMapController", function ($scope, $rootScope, $state, $stateParams, $timeout, $translate,
                                              $ionicSideMenuDelegate, Loader, Location, FanwallPost, FanwallUtils) {

    angular.extend($scope, {
        isLoading: true,
        collection: [],
        showInfoWindow: false,
        currentPost: null,
        filters: {
            latitude: 0,
            longitude: 0,
        }
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.hideInfoWindow = function () {
        $scope.showInfoWindow = false;
    };

    $scope.showPostModal = function (postGroup) {
        FanwallUtils.showPostModal(postGroup);
    };

    $scope.$on("$ionicView.enter", function () {
        $ionicSideMenuDelegate.canDragContent(false);
    });

    $scope.$on("$ionicView.leave", function () {
        $ionicSideMenuDelegate.canDragContent(true);
    });

    $scope.loadContent = function () {
        Location
        .getLocation({timeout: 10000}, true)
        .then(function (position) {
            $scope.filters.latitude = position.coords.latitude;
            $scope.filters.longitude = position.coords.longitude;
        }, function () {
            $scope.filters.latitude = 0;
            $scope.filters.longitude = 0;
        }).then(function () {
            FanwallPost
            .findAllMap($scope.filters, 0, false)
            .then(function (payload) {
                $scope.collection = payload.collection;
                $rootScope.$broadcast("fanwall.pageTitle", {pageTitle: payload.pageTitle});

                var markers = [];
                for (var position in $scope.collection) {
                    var postGroup = $scope.collection[position];
                    var marker = {
                        config: {
                            postGroup: angular.copy(postGroup)
                        },
                        onClick: (function (marker) {
                            $timeout(function () {
                                $scope.showPostModal(marker.config.postGroup);
                            });
                        })
                    };

                    marker.latitude = position.split("_")[0];
                    marker.longitude = position.split("_")[1];

                    var pinUrl;
                    switch (postGroup.length) {
                        case 1:
                            pinUrl = "./features/fanwall2/assets/templates/images/pin1.svg";
                            break;
                        case 2:
                            pinUrl = "./features/fanwall2/assets/templates/images/pin2.svg";
                            break;
                        case 3:
                            pinUrl = "./features/fanwall2/assets/templates/images/pin3.svg";
                            break;
                        case 4:
                            pinUrl = "./features/fanwall2/assets/templates/images/pin4.svg";
                            break;
                        case 5:
                        default:
                            pinUrl = "./features/fanwall2/assets/templates/images/pin5.svg";
                            break;
                    }

                    marker.icon = {
                        url: pinUrl,
                        anchor: new google.maps.Point(26, 52),
                        scaledSize: new google.maps.Size(40, 40)
                    };

                    markers.push(marker);
                }

                $scope.mapConfig = {
                    cluster: true,
                    markers: markers,
                    bounds_to_marker: true
                };
            }).finally(function () {
                $scope.isLoading = false;
            });
        });
    };

    $scope.loadContent();
});