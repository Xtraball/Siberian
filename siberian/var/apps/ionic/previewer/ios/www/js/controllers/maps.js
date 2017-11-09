/*global
 App, angular, BASE_PATH, BASE_URL, google
 */

angular.module("starter").controller("MapsController", function($log, $window, $scope, $stateParams, Modal, Maps,
                                                                GoogleMaps) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        error : false,
        show_directions : false,
        travel_mode : "DRIVING",
        show_instructions : false,
        origin : {
            "address": null,
            "latitude":null,
            "longitude":null
        },
        destination : {},
        card_design: false
    });

    Maps.setValueId($stateParams.value_id);
    
    $scope.disableTap = function(input_id) {
        var container = angular.element(document.getElementsByClassName('pac-container'));
        // disable ionic data tab
        container.attr('data-tap-disabled', 'true');
        // leave input field if google-address-entry is selected
        container.on("click", function(){
            $log.debug(input_id);
            document.getElementById(input_id).blur();
        });
    };

    $scope.loadContent = function() {

        Maps.find()
            .then(function(data) {

                $scope.is_loading = false;

                $scope.page_title = data.page_title;
                $scope.icon_url = data.icon_url;

                if(data.collection.latitude && data.collection.longitude) {
                    $scope.destination = {
                        "latitude": data.collection.latitude,
                        "longitude": data.collection.longitude,
                        "address": data.collection.address
                    };

                    switch(data.collection.unit) {
                        case "km":
                                $scope.unit_system = google.maps.UnitSystem.METRIC;
                            break;
                        default: // case "mi":
                            $scope.unit_system = google.maps.UnitSystem.IMPERIAL;
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

        GoogleMaps.calculateRoute(origin, $scope.destination, params)
            .then(function(route) {

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

                if(err === "gps_disabled") {

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

        if(!$scope.map) {
            return;
        }

        Modal
            .fromTemplateUrl("maps-info.html", {
                scope: $scope
            })
            .then(function(modal) {
                $scope.panel = modal;
                $scope.panel_content = document.getElementById("panel").outerHTML;
                $scope.panel.show();
            });

    };

    $scope.closePanel = function() {
        $scope.panel.remove();
    };

    $scope.changeTravelMode = function(mode) {

        if($scope.travel_mode === mode) {
            return;
        }

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
