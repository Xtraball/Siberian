"use strict";

App.service('GoogleMaps', function ($location, $q, $cordovaGeolocation) {

    var __self = {
        is_loaded: false,
        reset: function() {

            if(service.directionsRenderer) {
                service.directionsRenderer.setPanel(null);
                service.directionsRenderer.setMap(null);
            }

            for(var i = 0; i < service.markers; i++) {
                google.maps.event.removeListener(service.markers[i], 'click');
                service.markers[i].setMap(null);
            }

            service.map = null;
            service.panel_id = null;
            service.directionsRenderer = null;
            service.markers = new Array();
            __self.is_loaded = false;
        },
        _calculateRoute: function(origin, destination, params) {

            var deferred = $q.defer();

            if(!angular.isDefined(params)) {
                params = {
                    mode: google.maps.DirectionsTravelMode.WALKING,
                    unitSystem: google.maps.UnitSystem.METRIC
                };
            }

            if (!this.directionsService) {
                this.directionsService = new google.maps.DirectionsService();
            }
            var request = {
                origin: new google.maps.LatLng(origin.latitude, origin.longitude),
                destination: new google.maps.LatLng(destination.latitude, destination.longitude),
                travelMode: params.mode,
                unitSystem: params.unitSystem
            };

            this.directionsService.route(request, function (response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    deferred.resolve(response);
                } else {
                    var errorMessage = "An unexpected error occurred while calculating the route.";
                    console.error(errorMessage, status);
                    deferred.reject(errorMessage);
                }

            });

            return deferred.promise;
        },
        _updateBoundsFromPoints: function (bounds, p) {
            var latitude = parseFloat(p.latitude);
            var longitude = parseFloat(p.longitude);

            if (bounds[0][0] === null || bounds[0][0] > latitude) {
                bounds[0][0] = latitude;
            }

            if (bounds[1][0] === null || bounds[1][0] < latitude) {
                bounds[1][0] = latitude;
            }

            if (bounds[0][1] === null || bounds[0][1] > longitude) {
                bounds[0][1] = longitude;
            }

            if (bounds[1][1] === null || bounds[1][1] < longitude) {
                bounds[1][1] = longitude;
            }

            return bounds;
        },
        _extendBounds: function (bounds, margin) {
            if (margin) {
                var latitudeMargin = (bounds[1][0] - bounds[0][0]) * margin;
                if (latitudeMargin === 0) {
                    latitudeMargin = 0.02;
                }
                bounds[0][0] -= latitudeMargin;
                bounds[1][0] += latitudeMargin;

                var longitudeMargin = (bounds[1][1] - bounds[0][1]) * margin;
                if (longitudeMargin === 0) {
                    longitudeMargin = 0.01;
                }
                bounds[0][1] -= longitudeMargin;
                bounds[1][1] += longitudeMargin
            }

            return bounds;
        }
    };

    var service = {
        map: null,
        directionsRenderer: null,
        panel_id: null,
        markers: new Array(),
        createMap: function (element) {

            if(__self.is_loaded) {
                __self.reset();
            }

            var options = {
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            service.map = new google.maps.Map(document.getElementById(element), options);

            google.maps.event.addListener(service.map, "tilesloaded", function() {
                console.log("Maps is loaded");
                __self.is_loaded = true;
            });

            return service.map;

        },
        setCenter: function(coordinates) {
            var center = new google.maps.LatLng(coordinates.latitude, coordinates.longitude)
            service.map.setCenter(center);
        },
        setPanelId: function(panel_id) {
            service.panel_id = panel_id;
        },
        isLoaded: function() {
            return __self.is_loaded;
        },
        addMarker: function (/*map, */marker) {

            var latlng = new google.maps.LatLng(marker.latitude, marker.longitude);

            var icon = null;

            if (marker.icon && marker.icon.url) {
                var width = marker.icon.width ? marker.icon.width : 95;
                var height = marker.icon.height ? marker.icon.height : 49;
                icon = {
                    url: marker.icon.url,
                    scaledSize: new google.maps.Size(width, height) // Original is 530 x 272
                };
            }

            var mapMarker = new google.maps.Marker({
                position: latlng,
                map: service.map,
                icon: icon
            });

            if (marker.title) {

                var infoWindowContent = '<div><p style="color:black;">';

                if (marker.link) {
                    infoWindowContent += '<a href="' + marker.link + '">';
                }
                infoWindowContent += marker.title;
                if (marker.link) {
                    infoWindowContent += '</a>';
                }

                infoWindowContent += '</p></div>';

                var infoWindows = new google.maps.InfoWindow({
                    content: infoWindowContent
                });

                google.maps.event.addListener(mapMarker, 'click', function () {
                    infoWindows.open(service.map, mapMarker);
                });

            }

            if(marker.is_centered) {
                service.setCenter(marker);
            }

            service.markers.push(mapMarker);

            return mapMarker;
        },
        addRoute: function (route) {
            if(!service.directionsRenderer) {
                service.directionsRenderer = new google.maps.DirectionsRenderer();
            }
            service.directionsRenderer.setMap(service.map);
            service.directionsRenderer.setDirections(route);
            if(service.panel_id) {
                service.directionsRenderer.setPanel(document.getElementById(service.panel_id));
            }
        },
        fitToBounds: function (bounds) {
            var sw = new google.maps.LatLng(
                bounds.latitudeMin,
                bounds.longitudeMin
            );
            var ne = new google.maps.LatLng(
                bounds.latitudeMax,
                bounds.longitudeMax
            );
            var mapBounds = new google.maps.LatLngBounds(sw, ne);
            service.map.fitBounds(mapBounds);
        },
        geocode: function (address) {

            var deferred = $q.defer();

            if (!this.geocoder) {
                this.geocoder = new google.maps.Geocoder();
            }

            this.geocoder.geocode({
                'address': address
            }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();
                    deferred.resolve({
                        latitude: latitude,
                        longitude: longitude
                    });
                } else {
                    var errorMessage = "The address you're looking for does not exist.";
                    console.error(errorMessage);
                    deferred.reject(errorMessage);
                }
            });

            return deferred.promise;

        },
        reverseGeocode: function (position) {

            var deferred = $q.defer();

            if (!this.geocoder) {
                this.geocoder = new google.maps.Geocoder();
            }

            var latlng = {lat: position.latitude, lng: position.longitude};

            this.geocoder.geocode({
                'location': latlng
            }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    deferred.resolve(results);
                } else {
                    var errorMessage = "The address you're looking for does not exists.";
                    deferred.reject(errorMessage);
                }
            });

            return deferred.promise;
        },
        calculateRoute: function(origin, destination, params) {

            var deferred = $q.defer();

            if (origin) {

                __self._calculateRoute(origin, destination, params).then(function (route) {
                    deferred.resolve(route);
                }, function (err) {
                    deferred.reject(err);
                });

            } else {

                $cordovaGeolocation.getCurrentPosition().then(function (position) {

                    __self._calculateRoute(position.coords, destination, params).then(function (route) {
                        deferred.resolve(route);
                    }, function (err) {
                        deferred.reject(err);
                    });
                }, function (err) {
                    deferred.reject("gps_disabled");
                });

            }

            return deferred.promise;
        },
        getBoundsFromPoints: function (points, margin) {

            if (points) {

                var bounds = [[null, null], [null, null]];

                points.reduce(function (output, p) {

                    if (!p.latitude) {
                        console.warn('Invalid latitude.');
                    }

                    if (!p.longitude) {
                        console.warn('Invalid longitude.');
                    }
                    bounds = __self._updateBoundsFromPoints(bounds, p);

                    return output;
                }, []);

                if (points.length !== 0 && margin) {
                    bounds = __self._extendBounds(bounds, margin);
                }
                return {
                    latitudeMin: bounds[0][0],
                    latitudeMax: bounds[1][0],
                    longitudeMin: bounds[0][1],
                    longitudeMax: bounds[1][1]
                };
            }
            return null;
        }
    };



    return service;

});