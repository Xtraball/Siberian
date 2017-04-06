App.config(function($stateProvider) {

    $stateProvider.state('maps-view', {
        url: BASE_PATH+"/maps/mobile_view/index/value_id/:value_id",
        controller: 'MapsController',
        templateUrl: "templates/maps/l1/view.html"
    });

}).controller('MapsController', function($timeout, $window, $scope, $stateParams, $q, $ionicModal, Maps, GoogleMaps) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.disableTap = function(input_id) {
        var container = angular.element(document.getElementsByClassName('pac-container'));
        // disable ionic data tab
        container.attr('data-tap-disabled', 'true');
        // leave input field if google-address-entry is selected
        container.on("click", function(){
            console.log(input_id);
            document.getElementById(input_id).blur();
        });
    };

    $scope.value_id = Maps.value_id = $stateParams.value_id;
    $scope.is_loading = true;
    $scope.error = false;
    $scope.show_directions = false;
    $scope.travel_mode = "DRIVING";
    $scope.show_instructions = false;
    $scope.origin = {
        "address": null,
        "latitude":null,
        "longitude":null
    };
    $scope.destination = {};


    $scope.loadContent = function() {

        Maps.find().success(function(data) {

            $scope.is_loading = false;

            $scope.page_title = data.page_title;
            $scope.icon_url = data.icon_url;

            $scope.car_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "car.png");
            $scope.walk_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "walk.png");
            $scope.bus_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "bus.png");
            $scope.error_icon_url = BASE_URL+"/template/block/colorize/color/" + $window.colors.list_item.color.replace("#","") + "/path/" + btoa($scope.icon_url + "error.png");

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

                if(!$scope.map) {
                    $scope.map = GoogleMaps.createMap("google-maps");
                    GoogleMaps.setPanelId("panel");
                }

                $scope.getRoute(null);

            } else {
                $scope.is_loading = false;
                $scope.error = true;
            }

        });
    };

    $scope.getRoute = function(origin) {

        $scope.is_loading = true;
        $scope.error = false;

        var params = {
            mode: $scope.travel_mode,
            unitSystem: $scope.unit_system
        };

        GoogleMaps.calculateRoute(origin, $scope.destination, params).then(function(route) {

            GoogleMaps.addRoute(route);

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

                var bounds = GoogleMaps.getBoundsFromPoints([$scope.destination, $scope.destination.address]);
                GoogleMaps.setCenter(bounds);

                $scope.destination.title = $scope.destination.address;
                GoogleMaps.addMarker([$scope.destination]);

            } else {
                $scope.error = true;
                $scope.err_message = err;
            }

            $scope.is_loading = false;

        });
    };

    $scope.openPanel = function() {

        if(!$scope.map) return;

        $ionicModal.fromTemplateUrl("maps-info.html", {
            scope: $scope,
            animation: "slide-in-up"
        }).then(function(modal) {
            $scope.panel = modal;
            $scope.panel_content = document.getElementById("panel").outerHTML;
            $scope.panel.show();
         });

    };
    $scope.closePanel = function() {
        $scope.panel.remove();
    };

    $scope.changeTravelMode = function(mode) {

        if(!$scope.travel_mode == mode) return;

        switch(mode) {
            case 'WALKING':
                $scope.travel_mode = google.maps.TravelMode.WALKING;
                break;
            case 'DRIVING':
                $scope.travel_mode = google.maps.TravelMode.DRIVING;
                break;
            case 'TRANSIT':
                $scope.travel_mode = google.maps.TravelMode.TRANSIT;
                break;
        }
        $scope.is_loading = true;
        $scope.getRoute($scope.origin);
    };

    $scope.changeItinerary = function() {

        if(!$scope.origin.address) {
            $scope.getRoute(null);
        } else {
            $scope.getRoute($scope.origin);
        }

    };


    GoogleMaps.addCallback($scope.loadContent);

});
