"use strict";

App.service('GoogleMapService', function ($location, $routeParams, $q, Url) {

    return {
        createMap: function (domContainer, center) {
            // console.info('Create Google map with latitude %d and longitude %d.', center.latitude, center.longitude);

            var options = {
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            if (center.latitude && center.longitude) {
                options.center = new google.maps.LatLng(center.latitude, center.longitude);
            }

            var map = new google.maps.Map(domContainer, options);

            return map;
        },
        addMarker: function (map, marker) {

            var latlng = new google.maps.LatLng(marker.latitude, marker.longitude);

            var icon = null;

            if (marker.icon) {
                icon = {
                    url: marker.icon,
                    scaledSize: new google.maps.Size(95, 49) // Original is 530 x 272
                };
            }

            var mapMarker = new google.maps.Marker({
                position: latlng,
                map: map,
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
                    infoWindows.open(map, mapMarker);
                });

            }

            return mapMarker;
        },
        addRoute: function (map, route) {
            var directionsRenderer = new google.maps.DirectionsRenderer();
            directionsRenderer.setMap(map);
            directionsRenderer.setDirections(route);
            return directionsRenderer;
        },
        fitToBounds: function (map, bounds) {
            var sw = new google.maps.LatLng(
                bounds.latitudeMin,
                bounds.longitudeMin
                );
            var ne = new google.maps.LatLng(
                bounds.latitudeMax,
                bounds.longitudeMax
                );
            var mapBounds = new google.maps.LatLngBounds(sw, ne);
            map.fitBounds(mapBounds);
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
                var errorMessage = "The address you're looking for does not exists.";
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
    calculateRoute: function (origin, destination, params) {

        var deferred = $q.defer();

        if(!angular.isDefined(params)) {
            params = {
                "mode": google.maps.DirectionsTravelMode.WALKING,
                "unitSystem": google.maps.UnitSystem.METRIC
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
    }
};

});