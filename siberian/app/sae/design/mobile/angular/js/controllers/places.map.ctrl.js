App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/places/mobile_map/index/value_id/:value_id", {
        controller: 'PlacesMapController',
        templateUrl: BASE_URL + "/places/mobile_map/template",
        code: "places-map"
    });

}).controller('PlacesMapController', function ($scope, $routeParams, $location, $q, Places, Message, Url, GoogleMapService, MathsMapService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.message = new Message();

    $scope.value_id = Places.value_id = $routeParams.value_id;

    $scope.checkCoordinates = function (place) {

        var deferred = $q.defer();

        if (place && place.address) {
            if (place.address.latitude && place.address.longitude) {
                // nothing to do
                deferred.resolve(place);
            } else {
                // place is not geolocated: geocode it
                var address = decodeURI(place.address.address);

                GoogleMapService.geocode(address).then(function (coordinates) {

                    // fill place with coordinates
                    place.address.latitude = coordinates.latitude;
                    place.address.longitude = coordinates.longitude;

                    // success
                    deferred.resolve(place);

                }, function (err) {
                    deferred.reject(err);
                });
            }

        } else {
            // nothing to do
            deferred.reject('Invalid place');
        }

        return deferred.promise;
    };
    $scope.placeToMarker = function (place) {

        var deferred = $q.defer();

        $scope.checkCoordinates(place).then(function (place) {

            // build marker
            var marker = {
                title: place.title,
                link: Url.get('places/mobile_details/index', {
                    'value_id': $routeParams.value_id,
                    'place_id': place.id
                }),
                latitude: place.address.latitude,
                longitude: place.address.longitude
            };

            deferred.resolve(marker);

        }, function (err) {

            // do not display point on map
            deferred.resolve(null);
        });

        return deferred.promise;
    };

    $scope.loadContent = function () {
        Places.findAll().success(function (data) {

            var markersPromises = data.places.reduce(function (markersPromises, place) {

                markersPromises.push($scope.placeToMarker(place));
                return markersPromises;

            }, []);

            $q.all(markersPromises).then(function (markers) {

                // remove null markers (places without coordinates)
                markers = markers.reduce(function (markers, marker) {
                    if (marker) {
                        markers.push(marker);
                    }
                    return markers;
                }, []);

                if (markers.length === 0) {
                    $scope.message.setText('No place to display on map.')
                        .isError(true)
                        .show();
                    $scope.is_loading = false;
                } else {

                    var bounds = MathsMapService.getBoundsFromPoints(markers);

                    $scope.mapConfig = {
                        center: {
                            bounds: bounds
                        },
                        markers: markers
                    };
                    
                    $scope.is_loading = false;
                }

            }, function () {

                $scope.message.setText('An error occurred while loading places.')
                    .isError(true)
                    .show();
                $scope.is_loading = false;
            });



        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

    $scope.goToPlaceDetails = function (placeId) {
        $location.path(Url.get("places/mobile_details/index", {
            value_id: $routeParams.value_id,
            place_id: placeId
        }));
    };

});