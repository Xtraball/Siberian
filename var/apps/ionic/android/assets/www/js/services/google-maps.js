/* global google, App, angular */
App.service('GoogleMaps', function (_, $cordovaGeolocation, $location, $q, $rootScope, $translate, $window, Application) {
    "use strict";

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
            service.markers = [];
            __self.is_loaded = false;
        },
        _calculateRoute: function(origin, destination, params, rejectWithResponseAndStatus) {

            var deferred = $q.defer();

            if(!_.isObject(params))
                params = {
                    mode: google.maps.DirectionsTravelMode.WALKING,
                    unitSystem: google.maps.UnitSystem.METRIC
                };

            if(!_.isObject(params.request))
                params.request = {};


            if (!this.directionsService) {
                this.directionsService = new google.maps.DirectionsService();
            }

            var request = _.merge({
                origin: new google.maps.LatLng(origin.latitude, origin.longitude),
                destination: new google.maps.LatLng(destination.latitude, destination.longitude),
                travelMode: params.mode,
                unitSystem: params.unitSystem
            }, params.request);

            this.directionsService.route(request, function (response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    deferred.resolve(response);
                } else {
                    var errorMessage = $translate.instant(
                        status === "ZERO_RESULTS" ?
                            "There is no route available with these informations." :
                            "An unexpected error occurred while calculating the route."
                    );
                    console.error(errorMessage, status);
                    if(rejectWithResponseAndStatus === true) {
                        deferred.reject([response, status]);
                    } else {
                        deferred.reject(errorMessage);
                    }
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
                bounds[1][1] += longitudeMargin;
            }

            return bounds;
        }
    };

    var gmap_callbacks = [];
    var gmap_script_appended = false;
    var gmap_loaded = false;
    var _init_called = false;

    $window.initGMapCallback = function() {
        if(gmap_script_appended) {
            gmap_loaded = true;
            console.log("Gmap loaded, calling callbacks");
            while(gmap_callbacks.length > 0)  {
                var func = gmap_callbacks.shift();
                if(_.isFunction(func)) {
                    func.apply($window, arguments);
                }
            }
        }
    };

    var service = {
        USER_INTERACTED_EVENT: "GoogleMaps.UserInteracted",
        map: null,
        directionsRenderer: null,
        panel_id: null,
        markers: [],
        init: function() {
            if(typeof GoogleMaps == "undefined" && !gmap_script_appended) {
                if(_init_called)
                    return;

                _init_called = true;
                Application.loaded.then(function() {
                    var google_maps = document.createElement('script');
                    google_maps.type = "text/javascript";
                    google_maps.src = "https://maps.googleapis.com/maps/api/js?libraries=places&key="+Application.googlemaps_key+"&callback=initGMapCallback";
                    document.body.appendChild(google_maps);
                    gmap_script_appended = true;
                });
            }

            if(gmap_loaded) {
                $window.initGMapCallback();
            }
        },
        addCallback: function(func) {
            gmap_callbacks.push(func);
            service.init();
        },
        createMap: function (element, options) {
            if(!angular.isObject(options))
                options = {};

            if(__self.is_loaded) {
                __self.reset();
            }

            options = _.merge({
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }, options);

            service.map = new google.maps.Map(document.getElementById(element), options);

            google.maps.event.addListener(service.map, "tilesloaded", function() {
                console.log("Maps is loaded");
                __self.is_loaded = true;
            });

            var userInteracted = function(event_name) {
                return function() {
                    $rootScope.$broadcast(service.USER_INTERACTED_EVENT, event_name);
                };
            };

            google.maps.event.addListener(service.map, 'dblclick', userInteracted("dblclick"));
            google.maps.event.addListener(service.map, 'dragend', userInteracted("dragend"));
            google.maps.event.addDomListener(service.map.getDiv(),'mousewheel', userInteracted("wheel"), true);
            google.maps.event.addDomListener(service.map.getDiv(),'DOMMouseScroll', userInteracted("wheel"), true);

            return service.map;

        },
        setCenter: function(coordinates) {
            if(coordinates) {
                var center = new google.maps.LatLng(coordinates.latitude, coordinates.longitude);
                return service.map.setCenter(center);
            } else {
                return $cordovaGeolocation.getCurrentPosition().then(function (position) {
                    service.setCenter(position.coords);
                });
            }
        },
        setPanelId: function(panel_id) {
            service.panel_id = panel_id;
        },
        isLoaded: function() {
            return __self.is_loaded;
        },
        addMarker: function (/*map, */marker, index) {

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

            var options = _.merge({
                position: latlng,
                map: service.map,
                icon: icon
            }, marker.markerOptions);

            var mapMarker = new google.maps.Marker(options);

            if (marker.title) {

                var infoWindowContent = '<div><p style="color:black;">';

                if (marker.link) {
                    infoWindowContent += '<a href="' + marker.link + '">';
                }
                infoWindowContent += marker.title;
                if (marker.link) {
                    infoWindowContent += '</a>';
                }

                var markerHasAction = _.isObject(marker.action) && _.isString(marker.action.label) && _.isFunction(marker.action.onclick);

                if (markerHasAction) {
                    var id = "map_marker_infowindow_action_"+Math.ceil((+new Date())*Math.random());
                    infoWindowContent += '<div style="margin-top: 15px; "><button id="'+id+'" class="button button-custom">'+marker.action.label+'</button></div>';
                }

                infoWindowContent += '</p></div>';

                var infoWindows = new google.maps.InfoWindow({
                    content: infoWindowContent
                });

                if(markerHasAction) {
                    google.maps.event.addListener(infoWindows, 'domready', function() {
                        document.getElementById(id).addEventListener("click", marker.action.onclick);
                    });
                }

                google.maps.event.addListener(mapMarker, 'click', function () {
                    infoWindows.open(service.map, mapMarker);
                });

            }

            if(marker.is_centered) {
                service.setCenter(marker);
            }

            if(+index < 0) {
                service.markers.push(mapMarker);
            } else {
                service.markers.splice(index, 0, mapMarker);
            }

            return mapMarker;
        },
        removeMarker: function(mapMarker) {
            var index = service.markers.indexOf(mapMarker);

            if(index >= 0) {
                service.markers[index].setMap(null);
                service.markers.splice(index, 1);
                return true;
            }

            return false;
        },
        replaceMarker: function(mapMarker, marker) {
            if(service.removeMarker(mapMarker)) {
                return service.addMarker(marker);
            }

            return false;
        },
        addRoute: function (route, custom_directions_renderer, custom_panel_div_id) {
            var renderer = null;

            if(_.isObject(custom_directions_renderer) && _.isFunction(custom_directions_renderer.setDirections)) {
                renderer = custom_directions_renderer;
            } else {
                if(!service.directionsRenderer) {
                    service.directionsRenderer = new google.maps.DirectionsRenderer();
                }
                renderer = service.directionsRenderer;
            }

            renderer.setMap(service.map);
            renderer.setDirections(route);
            var panelDiv = document.getElementById(custom_panel_div_id || service.panel_id);
            if(panelDiv) {
                renderer.setPanel(panelDiv);
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
                    var errorMessage = $translate.instant("The address you're looking for does not exist.");
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
                    var errorMessage = $translate.instant("The address you're looking for does not exists.");
                    deferred.reject(errorMessage);
                }
            });

            return deferred.promise;
        },
        calculateRoute: function(origin, destination, params, rejectWithResponseAndStatus) {

            var deferred = $q.defer();

            if (origin) {

                __self._calculateRoute(origin, destination, params, rejectWithResponseAndStatus).then(function (route) {
                    deferred.resolve(route);
                }, function (err) {
                    deferred.reject(err);
                });

            } else {

                $cordovaGeolocation.getCurrentPosition().then(function (position) {

                    __self._calculateRoute(position.coords, destination, params, rejectWithResponseAndStatus).then(function (route) {
                        deferred.resolve(route);
                    }, function (err) {
                        deferred.reject(err);
                    });
                }, function (err) {
                    if(
                        angular.isObject(err) && err.code === 1 &&
                            angular.isString(err.message) && err.message.indexOf("secure origin")
                    ) {
                        deferred.reject("Your location could not be found because your application doesn't use SSL.");
                    }
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

    service.init();

    return service;

});
