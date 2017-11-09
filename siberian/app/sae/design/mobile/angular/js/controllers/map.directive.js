"use strict";

App.directive('sbMap', function (GoogleMapService, MathsMapService) {

    return {
        restrict: 'A',
        scope: {
            map: '=',
            config:'='
        },
        link: {
            post: function ($scope, element, attrs) {

                $scope.map = null;
                $scope.mapMarkers = null;
                $scope.panel_id = attrs.panelId;

                $scope.watchMarkers = function () {
                    $scope.$watch('config.markers', function (markers, old) {

                        if ($scope.map && !$scope.mapMarkers && markers) {

                            // create all markers

                            $scope.mapMarkers = markers.reduce(function (mapMarkers, marker) {

                                var mapMarker = GoogleMapService.addMarker($scope.map, marker);

                                mapMarkers.push(mapMarker);

                                return mapMarkers;

                            }, []);

                        }

                    }, false);
                };

                $scope.watchRoutes = function () {
                    $scope.$watch('config.route', function (route) {

                        if ($scope.map && route) {

                            if(!$scope.mapRoute) {
                                $scope.mapRoute = GoogleMapService.addRoute($scope.map, route);
                            } else {
                                $scope.mapRoute.setMap($scope.map);
                                $scope.mapRoute.setDirections(route);
                            }

                            if($scope.panel_id) {
                                $scope.mapRoute.setPanel(document.getElementById($scope.panel_id));
                            }

                        }

                    }, false);
                };
                $scope.watchCenter = function () {
                    $scope.$watch('config.center', function (center, old) {
                        if (center && ((center.latitude && center.longitude) || center.bounds)) {

                            if (!$scope.map) {
                                // create map

                                $scope.map = GoogleMapService.createMap(element[0], center);

                                if (center.bounds) {
                                    // fit to bounds
                                    GoogleMapService.fitToBounds($scope.map, center.bounds);
                                }

                                $scope.watchMarkers();
                                $scope.watchRoutes();

                            } else {
                                // TODO update map center (not necessary for now)
                            }
                        }
                    });
                };

                $scope.watchCenter();

            }
        }
    };
});