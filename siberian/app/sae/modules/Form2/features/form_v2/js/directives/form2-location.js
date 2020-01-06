/**
 * @directive form2-location
 * @author Xtraball SAS
 * @version 4.18.5
 */
angular
.module('starter')
.directive('form2Location', function () {
    return {
        restrict: 'E',
        replace: true,
        scope: {
            locationIsLoading: false,
            field: '=?'
        },
        templateUrl: './features/form_v2/assets/templates/l1/directive/geolocation.html',
        controller: function($scope, $filter, Dialog, Location) {

            $scope.getLocation = function () {
                if (Location.isEnabled) {
                    $scope.locationIsLoading = true;
                    Location
                        .getLocation()
                        .then(function (position) {
                            var lat = Number.parseFloat(position.coords.latitude).toFixed(5);
                            var lng = Number.parseFloat(position.coords.longitude).toFixed(5);

                            $scope.field.value = {
                                address: null,
                                coords: {
                                    lat: lat,
                                    lng: lng
                                }
                            };

                            GoogleMaps
                                .reverseGeocode(position.coords)
                                .then(function (results) {
                                    if (results[0] && results[0].formatted_address) {
                                        $scope.field.value.address = results[0].formatted_address;
                                    }
                                });

                        }, function (e) {
                            $scope.field.value = null;
                        }).then(function () {
                            $scope.locationIsLoading = false;
                        });
                } else {

                }
            };

            $scope.formatLocation = function () {
                var html;
                if ($scope.field.value.address) {
                    html = $scope.field.value.address + '<br />' +
                        $scope.field.value.coords.lat + ', ' +
                        $scope.field.value.coords.lng;
                } else {
                    html = $scope.field.value.coords.lat + ', ' +
                        $scope.field.value.coords.lng;
                }

                return $filter('trusted_html')(html);
            };
        }
    };
});