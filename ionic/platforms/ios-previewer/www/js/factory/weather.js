/*global
 App, device, angular, btoa
 */

/**
 * Weather
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Weather", function($q, $pwaRequest, $cordovaGeolocation, GoogleMaps) {

    var factory = {
        value_id        : null,
        extendedOptions : {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    factory.find = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Weather.find] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if(payload !== false) {

            return $pwaRequest.resolve(payload);

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("weather/mobile_view/find", angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));

        }


    };

    factory.getWeather = function(woeid, unit) {
        var deferred = $q.defer();

        if(woeid) {
            factory.getWeatherFromWoeid(woeid, unit).then(function(data) {
                if(!data.query.results.channel.astronomy) {
                    deferred.reject("Unable to get weather for this location.");
                } else {
                    deferred.resolve(data);
                }
            }, function() {
                deferred.reject("Unable to get weather.");
            });
        } else {

            /***
             * @todo use location service
             */
            $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {

                GoogleMaps.reverseGeocode(position.coords).then(function(data) {
                    var postal_code = null;
                    var country = null;

                    if(data[0]["address_components"][6]) {
                        if(data[0]["address_components"][6]["types"][0] == "postal_code") {
                            postal_code = data[0]["address_components"][6]["long_name"];
                        }
                    }

                    if(data[0]["address_components"][5]) {
                        country = data[0]["address_components"][5]["short_name"];
                    }

                    if(country) {
                        var param = "";
                        if(postal_code) {
                            param = postal_code + "," + country;
                        } else {
                            param = country;
                        }

                        factory.getWoeid(param)
                            .then(function(data) {

                            var woeid = null;
                            if(data["query"]["count"]> 0) {
                                if(data["query"]["results"]["place"].length > 1) {
                                    woeid = data["query"]["results"]["place"][0]["woeid"];
                                } else {
                                    woeid = data["query"]["results"]["place"]["woeid"];
                                }
                            }

                            if(woeid) {
                                factory.getWeatherFromWoeid(woeid, unit).then(function(data) {

                                    if(!data.query.results.channel.astronomy) {
                                        deferred.reject("Unable to get weather for this location.");
                                    } else {
                                        deferred.resolve(data);
                                    }
                                }, function() {
                                    deferred.reject("Unable to get weather.");
                                });
                            } else {
                                deferred.reject("Unable to get your woeid.");
                            }
                        }, function() {
                            deferred.reject("Unable to get your woeid.");
                        });

                    } else {
                        deferred.reject();
                    }
                }, function(message) {
                    deferred.reject(message);
                });

            }, function (err) {
                deferred.reject(err);
            });

        }

        return deferred.promise;
    };

    factory.getWoeid = function(param) {

        var yql = encodeURI("select woeid from geo.places where text='" + param + "'");

        return $pwaRequest.post("/weather/mobile_view/proxy", {
            data: {
                request: btoa("https://query.yahooapis.com/v1/public/yql?q=" + yql + "&format=json")
            },
            cache: false
        });
    };

    factory.getWeatherFromWoeid = function(woeid, unit) {

        var yql = encodeURI("select * from weather.forecast where woeid='" + woeid + "' and u='" + unit + "'");

        return $pwaRequest.post("/weather/mobile_view/proxy", {
            data: {
                request: btoa("https://query.yahooapis.com/v1/public/yql?q=" + yql + "&format=json&lang=fr-FR")
            },
            cache: false
        });
    };

    return factory;
});
