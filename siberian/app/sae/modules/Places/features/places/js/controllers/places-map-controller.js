/**
 * @version 4.20.10
 */
angular
    .module('starter')
    .controller('PlacesMapController', function ($rootScope, $scope, $ionicSideMenuDelegate, $state,
                                                 $stateParams, $translate, $timeout, Dialog, Location, Loader,
                                                 Places, GoogleMaps) {

        angular.extend($scope, {
            isLoading: true,
            isShortLoading: false,
            emptyResult: false,
            collection: [],
            showInfoWindow: false,
            currentPlace: null,
            isUserCentered: false,
            filters: {
                e: 0,
                n: 0,
                s: 0,
                w: 0,
                zoom: Places.settings.defaultMapZoom || 8
            },
            idleTimer: null
        });

        window.markers = [];
        window.markersConfig = [];
        window.markerClusterer;

        Places.setValueId($stateParams.value_id);

        $scope.goToList = function () {
            $state.go('places-list', {
                value_id: Places.value_id
            });
        };

        $scope.hideInfoWindow = function () {
            $scope.showInfoWindow = false;
        };

        $scope.$on('$ionicView.enter', function () {
            $ionicSideMenuDelegate.canDragContent(false);
        });

        $scope.$on('$ionicView.leave', function () {
            $ionicSideMenuDelegate.canDragContent(true);
        });

        $scope.zoomIn = function () {
            $scope.crMap.setZoom($scope.crMap.getZoom() + 1);
        };

        $scope.zoomOut = function () {
            $scope.crMap.setZoom($scope.crMap.getZoom() - 1);
        };

        $scope.markerClick = function (marker) {
            $timeout(function () {
                if (Places.settings.mapAction &&
                    Places.settings.mapAction === 'gotoPlace') {
                    $scope.goToPlace(marker.config.place.id);
                } else {
                    $scope.showInfoWindow = true;
                    $scope.currentPlace = marker.config.place;
                }
            });
        };

        $scope.loadContent = function (refresh) {

            Places
                .findAllMaps($scope.filters, refresh)
                .then(function (payload) {
                    // Clear old markers
                    for (var i = 0; i < window.markers.length; i++) {
                        window.markers[i].setMap(null);
                    }
                    window.markers = [];
                    window.markersConfig = [];

                    $scope.collection = payload.places;

                    for (var j = 0; j < $scope.collection.length; j++) {
                        var place = $scope.collection[j];
                        var marker = {
                            config: {
                                id: angular.copy(place.id),
                                place: angular.copy(place)
                            }
                        };

                        if (place.address.latitude && place.address.longitude) {
                            marker.latitude = place.address.latitude;
                            marker.longitude = place.address.longitude;
                        } else {
                            marker.address = place.address.address;
                        }

                        switch (place.mapIcon) {
                            case 'pin':
                                if (place.pin) {
                                    marker.icon = {
                                        url: place.pin,
                                        scaledSize: new google.maps.Size(42, 42)
                                    };
                                }
                                break;
                            case 'image':
                                if (place.picture) {
                                    marker.icon = {
                                        url: place.picture,
                                        scaledSize: new google.maps.Size(70, 44)
                                    };
                                }
                                break;
                            case 'thumbnail':
                                if (place.thumbnail) {
                                    marker.icon = {
                                        url: place.thumbnail,
                                        scaledSize: new google.maps.Size(42, 42)
                                    };
                                }
                                break;
                            case 'default':
                            default:
                                // Defaults to google map icons
                                break;
                        }

                        var tmpMarker = new google.maps.Marker({
                            position: new google.maps.LatLng(marker.latitude, marker.longitude),
                            map: $scope.crMap,
                            index: angular.copy(j),
                            icon: marker.icon
                        });
                        google.maps.event.addListener(tmpMarker, 'click', function () {
                            $scope.markerClick(window.markersConfig[this.index]);
                        });
                        window.markersConfig.push(angular.copy(marker));
                        window.markers.push(tmpMarker);
                        window.markerClusterer.addMarker(tmpMarker);
                    }

                }).finally(function () {
                if ($scope.isLoading === true) {
                    $scope.isLoading = false;
                    Loader.hide();
                }

                $scope.isShortLoading = false;
            });
        };

        $scope.goToPlace = function (placeId) {
            $state.go('places-view', {
                value_id: Places.value_id,
                page_id: placeId
            });
        };

        $scope.centerMe = function (startup) {
            // If user already hit the button, skip!
            if (startup !== undefined && $scope.isUserCentered === true) {
                return;
            }

            $scope.isUserCentered = true;
            Location
                .getLocation()
                .then(function (position) {
                    $scope.crMap.setCenter(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
                    $scope.crMap.setZoom(Places.settings.defaultCenterZoom || 8);
                }, function () {
                    if (startup === undefined) {
                        Dialog.alert(
                            'Location',
                            'Sorry we are unable to locate you, please check your GPS settings & authorization.',
                            'OK',
                            -1,
                            'Places');
                    }
                });
        };

        $scope.updateBounds = function () {
            var jsonBounds = $scope.crMap.getBounds().toJSON();

            $scope.filters.e = jsonBounds.east;
            $scope.filters.n = jsonBounds.north;
            $scope.filters.s = jsonBounds.south;
            $scope.filters.w = jsonBounds.west;
            $scope.filters.zoom = $scope.crMap.getZoom();
        };

        $scope.initMap = function () {
            var lat = 43.604652;
            var lng = 1.444209;
            if (Places.settings.hasOwnProperty('lat') && Places.settings.hasOwnProperty('lng')) {
                lat = parseFloat(Places.settings.lat);
                lng = parseFloat(Places.settings.lng);
            }

            $scope.mapSettings = {
                zoom: Places.settings.defaultMapZoom || 8,
                center: {
                    lat: lat,
                    lng: lng
                },
                cluster: true,
                disableDefaultUI: true,
                bounds_to_marker: true
            };
            $scope.crMap = GoogleMaps.createMap('crMap', $scope.mapSettings);

            // Debounced idler
            google.maps.event.addListener($scope.crMap, 'idle', function () {
                $timeout.cancel($scope.idleTimer);

                // Call auto center just in case!
                if (!$scope.isUserCentered) {
                    $scope.centerMe(true);
                }

                $scope.idleTimer = $timeout(function () {
                    $scope.updateBounds();
                    $scope.isShortLoading = !$scope.isLoading;
                    $scope.loadContent(true);
                    window.markerClusterer = new MarkerClusterer($scope.crMap, [], {
                        maxZoom: 15,
                        minimumClusterSize: 2,
                        imagePath: './img/cluster/m'
                    });
                }, 750);
            });
        };

        GoogleMaps.addCallback($scope.initMap);

        Loader.show($translate.instant('Loading...', 'places'));
    });
