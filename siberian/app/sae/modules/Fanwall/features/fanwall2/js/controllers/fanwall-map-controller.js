/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallMapController", function ($scope, $state, $stateParams, $timeout, $translate, Loader, Location,
                                              FanwallPost, FanwallUtils) {

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

    $scope.loadContent = function () {
        Loader.show($translate.instant("Fetching your location...", "fanwall"));

        Location
        .getLocation({timeout: 10000}, true)
        .then(function (position) {
            $scope.filters.latitude = position.coords.latitude;
            $scope.filters.longitude = position.coords.longitude;
        }, function () {
            $scope.filters.latitude = 0;
            $scope.filters.longitude = 0;
        }).then(function () {
            Loader.hide();

            FanwallPost
            .findAllMap($scope.filters, 0, false)
            .then(function (payload) {
                $scope.collection = payload.collection;

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