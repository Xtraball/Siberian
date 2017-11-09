App.factory('Weather', function($rootScope, $q, $http, Url, GoogleMapService, Application) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("weather/mobile_view/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.getWeather = function(woeid, unit) {
        var deferred = $q.defer();

        if(woeid) {
            factory.getWeatherFromWoeid(woeid, unit).success(function(data) {
                if(!data.query.results.channel.astronomy) {
                    deferred.reject("Unable to get weather for this location.");
                } else {
                    deferred.resolve(data);
                }
            }).error(function() {
                deferred.reject("Unable to get weather.");
            });
        } else {

            Application.getLocation(function(position) {

                GoogleMapService.reverseGeocode(position).then(function(data) {
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

                        factory.getWoeid(param).success(function(data) {
                            var woeid = null;
                            if(data["query"]["count"]> 0) {
                                if(data["query"]["results"]["place"].length > 1) {
                                    woeid = data["query"]["results"]["place"][0]["woeid"];
                                } else {
                                    woeid = data["query"]["results"]["place"]["woeid"];
                                }
                            }

                            if(woeid) {
                                factory.getWeatherFromWoeid(woeid, unit).success(function(data) {
                                    if(!data.query.results.channel.astronomy) {
                                        deferred.reject("Unable to get weather for this location.");
                                    } else {
                                        deferred.resolve(data);
                                    }
                                }).error(function() {
                                    deferred.reject("Unable to get weather.");
                                });
                            } else {
                                deferred.reject("Unable to get your woeid.");
                            }
                        }).error(function() {
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
        return $http({
            method: 'GET',
            url: "http://query.yahooapis.com/v1/public/yql?q=select woeid from geo.places where text='" + param + "'&format=json",
            cache: false,
            responseType:'json'
        });
    };

    factory.getWeatherFromWoeid = function(woeid, unit) {
        return $http({
            method: 'GET',
            url: "https://query.yahooapis.com/v1/public/yql?q=select * from weather.forecast where woeid='" + woeid + "' and u='" + unit + "'&format=json&lang=fr-FR",
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
