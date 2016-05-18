App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/places/mobile_detailsmap/index/value_id/:value_id/place_id/:place_id", {
        controller: 'PlacesDetailsMapController',
        templateUrl: BASE_URL + "/places/mobile_detailsmap/template",
        code: "places-details-map"
    });

}).controller('PlacesDetailsMapController', function ($scope, $routeParams, $location, $q, Places, Message, Url, GoogleMapService, MathsMapService, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.message = new Message();

    $scope.value_id = Places.value_id = $routeParams.value_id;

    $scope.getCurrentPosition = function () {

        var deferred = $q.defer();

        Application.getLocation(function(position) {
            deferred.resolve(position);
        }, function (err) {
            deferred.reject(err);
        });

        return deferred.promise;
    };


    $scope.getPlace = function (place_id) {

        var deferred = $q.defer();

        // retrieve place data
        Places.find(place_id).then(function (response) {

            var place = response.data.place;

            if (!place || !place.address) {
                deferred.reject('Place not found.');
            } else if(place.address.latitude && place.address.longitude) {
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

        }, function (err) {
            deferred.reject(err);
        });

        return deferred.promise;
    };

    $scope.loadContent = function () {

        var promises = [];

        // get current position
        promises.push($scope.getCurrentPosition());

        // get place coordinates
        promises.push($scope.getPlace($routeParams.place_id));

        // synchronize queries
        $q.all(promises).then(function (results) {

            var coordinates = results[0];
            var place = results[1];

            if (place && place.address) {

                GoogleMapService.calculateRoute(coordinates, place.address).then(function (route) {

                    var bounds = MathsMapService.getBoundsFromPoints([coordinates, place.address]);

                    $scope.mapConfig = {
                        center: {
                            bounds: bounds
                        },
                        routes: [route]
                    };
                }, function (err) {
                    $scope.message.setText('Unable to calculate the route.')
                        .isError(true)
                        .show();
                    $scope.is_loading = false;
                }).finally(function () {
                    $scope.is_loading = false;
                });

            } else {
                $scope.message.setText('No address to display on map.')
                    .isError(true)
                    .show();
                $scope.is_loading = false;
            }


        }, function (err) {
            $scope.message.setText("You must share your location to access this page.")
                .isError(true)
                .show();
            $scope.is_loading = false;
        })

    };

    $scope.loadContent();

    $scope.header_right_button = {
        action: $scope.goToMap,
        title: "Map"
    };

});