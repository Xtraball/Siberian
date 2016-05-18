App.directive('sbMaps', function() {
    return {
        restrict: 'A',
        scope: {
            map: "=",
            // markers          - Array (Collection of markers)
            //    marker        - Object
            //       address    - String
            //       latitude   - Float
            //       longitude  - Float
            //       icon       - Object
            //          url     - String
            //          width   - Float (default 95)
            //          height  - Float (default 49)
            //       title      - String
            //       link       - String
            //       is_centered- Bool
            // bounds_to_marker - Bool
            // bounds           - Object
            //    latitude1     - Float
            //    longitude1    - Float
            //    latitude2     - Float
            //    longitude2    - Float
            // coordinates      - Object
            //    origin        - Object
            //       address    - String
            //       latitude   - Float
            //       longitude  - Float
            //       title      - String
            //    destination   - Object
            //       address    - String
            //       latitude   - Float
            //       longitude  - Float
            //       title      - String
            // travel_mode      - String
            // unit_system      - Int
            config: "="
        },
        controller: function($scope, GoogleMaps) {

            console.log("$scope.config: ", $scope.config);

            $scope.map = GoogleMaps.createMap("google-maps");

            if($scope.config.coordinates) {

                if(!$scope.config.coordinates.origin || ((!$scope.config.coordinates.origin.latitude || !$scope.config.coordinates.origin.longitude) && !$scope.config.coordinates.origin.address)) {
                    console.error("An origin (latitude / longitude or address) is required to use Maps");
                    return;
                }
                if(!$scope.config.coordinates.destination || ((!$scope.config.coordinates.destination.latitude || !$scope.config.coordinates.destination.longitude) && !$scope.config.coordinates.destination.address)) {
                    console.error("A destination (latitude / longitude or address) is required to use Maps");
                    return;
                }

                GoogleMaps.calculateRoute($scope.config.coordinates.origin, $scope.config.coordinates.destination).then(function(route) {
                    GoogleMaps.addRoute(route);
                });

            }

            if($scope.config.markers && $scope.config.markers.constructor === Array) {

                for(var i = 0; i < $scope.config.markers.length; i++) {
                    var marker = $scope.config.markers[i];
                    GoogleMaps.addMarker(marker);
                }

                if($scope.config.bounds_to_marker) {
                    var bounds = GoogleMaps.getBoundsFromPoints($scope.config.markers);
                    GoogleMaps.fitToBounds(bounds);
                }

            }

        }
    }
});
