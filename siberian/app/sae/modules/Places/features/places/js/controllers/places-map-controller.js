/**
 * @version 4.18.5
 */
angular
.module('starter')
.controller('PlacesMapController', function ($rootScope, $scope, $state, $stateParams, $translate, $timeout, Location,
                                               Places, GoogleMaps) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        collection: [],
        showInfoWindow: false,
        currentPlace: null,
        filters: {
            latitude: 0,
            longitude: 0,
        }
    });

    Places.setValueId($stateParams.value_id);

    $scope.goToList = function () {
        $state.go("places-list", {
            value_id: $scope.value_id
        });
    };

    $scope.hideInfoWindow = function () {
        $scope.showInfoWindow = false;
    };

    $scope.loadContent = function () {
        if (Location.isEnabled) {
            Location
                .getLocation({timeout: 10000}, true)
                .then(function (position) {
                    $scope.filters.latitude = position.coords.latitude;
                    $scope.filters.longitude = position.coords.longitude;
                }, function () {
                    $scope.filters.latitude = 0;
                    $scope.filters.longitude = 0;
                }).then(function () {
                    $scope.loadContentCallback();
                });
        } else {
            $scope.filters.latitude = 0;
            $scope.filters.longitude = 0;
            $scope.loadContentCallback();
        }
    };

    $scope.loadContentCallback = function () {
        Places
            .findAllMaps($scope.filters, false)
            .then(function (data) {
                $scope.page_title = data.page_title;
                $scope.collection = data.places;

                Places.mapCollection = $scope.collection;

                var markers = [];

                for (var i = 0; i < $scope.collection.length; i = i + 1) {
                    var place = $scope.collection[i];
                    var marker = {
                        config: {
                            id: angular.copy(place.id),
                            place: angular.copy(place)
                        },
                        onClick: (function (marker) {
                            $timeout(function () {
                                if (Places.settings.mapAction &&
                                    Places.settings.mapAction === 'gotoPlace') {
                                    $scope.goToPlace(marker.config.place.id);
                                } else {
                                    $scope.showInfoWindow = true;
                                    $scope.currentPlace = marker.config.place;
                                }
                            });
                        })
                    };

                    if (place.address.latitude && place.address.longitude) {
                        marker.latitude = place.address.latitude;
                        marker.longitude = place.address.longitude;
                    } else {
                        marker.address = place.address.address;
                    }

                    switch (place.mapIcon) {
                        case "pin":
                            if (place.pin) {
                                marker.icon = {
                                    url: place.pin,
                                    width: 42,
                                    height: 42
                                };
                            }
                            break;
                        case "image":
                            if (place.picture) {
                                marker.icon = {
                                    url: place.picture,
                                    width: 70,
                                    height: 44
                                };
                            }
                            break;
                        case "thumbnail":
                            if (place.thumbnail) {
                                marker.icon = {
                                    url: place.thumbnail,
                                    width: 42,
                                    height: 42
                                };
                            }
                            break;
                        case "default": default:
                            // Defaults to google map icons
                            break;
                    }

                    markers.push(marker);
                }

                $scope.map_config = {
                    cluster: true,
                    markers: markers,
                    bounds_to_marker: true
                };
            }).finally(function () {
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();

    $rootScope.$on('location.request.success', function () {
        $scope.loadContent();
    });

    $scope.goToPlace = function (placeId) {
        $state.go("places-view", {
            value_id: $scope.value_id,
            page_id: placeId
        });
    };
});
