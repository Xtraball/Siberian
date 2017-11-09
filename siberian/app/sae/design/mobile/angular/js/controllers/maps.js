App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/maps/mobile_view/index/value_id/:value_id", {
        controller: 'MapsController',
        templateUrl: BASE_URL+"/maps/mobile_view/template",
        code: "maps"
    });

}).controller('MapsController', function($window, $scope, $routeParams, $q, Maps, MathsMapService, GoogleMapService) {

    $scope.value_id = Maps.value_id = $routeParams.value_id;
    $scope.is_loading = true;
    $scope.error = false;
    $scope.show_directions = false;
    $scope.map = {};
    $scope.mode = "DRIVING";
    $scope.show_instructions = false;
    $scope.origin = {
        "address": null,
        "latitude":null,
        "longitude":null
    };
    $scope.destination = {};

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.loadContent = function() {

        Maps.find().success(function(data) {
            $scope.page_title = data.page_title;
            $scope.icon_url = data.icon_url;

            $scope.car_icon_url = "/template/block/colorize/color/" + $window.colors.background.color.replace("#","") + "/path/" + btoa($scope.icon_url + "car.png");
            $scope.walk_icon_url = "/template/block/colorize/color/" + $window.colors.background.color.replace("#","") + "/path/" + btoa($scope.icon_url + "walk.png");
            $scope.bus_icon_url = "/template/block/colorize/color/" + $window.colors.background.color.replace("#","") + "/path/" + btoa($scope.icon_url + "bus.png");
            $scope.error_icon_url = "/template/block/colorize/color/" + $window.colors.background.color.replace("#","") + "/path/" + btoa($scope.icon_url + "error.png");

            if(data.collection.latitude && data.collection.longitude) {
                $scope.destination = {
                    "latitude": data.collection.latitude,
                    "longitude": data.collection.longitude,
                    "address": data.collection.address
                };

                switch(data.collection.unit) {
                    case "km": $scope.unit_system = google.maps.UnitSystem.METRIC; break;
                    case "mi":
                    default: $scope.unit_system = google.maps.UnitSystem.IMPERIAL; break;
                }

                $scope.getRoute(null);
            } else {
                $scope.is_loading = false;
                $scope.error = true;
            }

        });
    };

    $scope.loadContent();

    $scope.getRoute = function(origin) {

        $scope.error = false;

        var params = {
            "mode": $scope.mode,
            "unitSystem": $scope.unit_system
        };

        Maps.calculateRoute(origin,$scope.destination, params).then(function(route) {
            var bounds = MathsMapService.getBoundsFromPoints([$scope.destination, $scope.destination.address]);

            $scope.mapConfig = {
                center: {
                    bounds: bounds
                },
                route: route
            };

            if(route.routes[0]) {
                if(route.routes[0].legs[0].duration.text) {
                    $scope.duration = route.routes[0].legs[0].duration.text;
                }
                if(route.routes[0].legs[0].distance.text) {
                    $scope.distance = route.routes[0].legs[0].distance.text;
                }
            }

            $scope.origin.latitude = route.request.origin.lat();
            $scope.origin.longitude = route.request.origin.lng();

            $scope.is_loading = false;

        }, function(err) {
            if(err == "gps_disabled") {
                var bounds = MathsMapService.getBoundsFromPoints([$scope.destination, $scope.destination.address]);
                $scope.destination.title = $scope.destination.address;
                $scope.mapConfig = {
                    center: {
                        bounds: bounds
                    },
                    markers: [$scope.destination]
                };
            } else {
                $scope.error = true;
                $scope.err_message = err;
            }
            $scope.is_loading = false;
        });
    };

    $scope.toggleInstructions = function() {
        $scope.show_instructions = !$scope.show_instructions;
    };

    $scope.toggleMode = function(mode) {
        switch(mode) {
            case 'WALKING':
                $scope.mode = google.maps.TravelMode.WALKING;
                break;
            case 'DRIVING':
                $scope.mode = google.maps.TravelMode.DRIVING;
                break;
            case 'TRANSIT':
                $scope.mode = google.maps.TravelMode.TRANSIT;
                break;
        }
        $scope.is_loading = true;
        $scope.getRoute($scope.origin);
    };

    $scope.changeItinerary = function() {
        $scope.is_loading = true;
        if(!$scope.origin.address) {
            $scope.getRoute(null);
        } else {
            $scope.getRoute($scope.origin);
        }
    }

});