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

        factory
            .getWeatherFromWoeid(woeid, unit)
            .then(function(data) {
                if(!data.query.results.channel.astronomy) {
                    deferred.reject("Unable to get weather for this location.");
                } else {
                    deferred.resolve(data);
                }
            }, function() {
                deferred.reject("Unable to get weather.");
            });

        return deferred.promise;
    };

    factory.getWoeid = function(param) {

        var yql = encodeURI("SELECT woeid FROM geo.places WHERE text='" + param + "'");

        return $pwaRequest.post("/weather/mobile_view/proxy", {
            data: {
                request: btoa("https://query.yahooapis.com/v1/public/yql?q=" + yql + "&format=json")
            },
            cache: false
        });
    };

    factory.getWeatherFromWoeid = function(woeid, unit) {

        var yql = encodeURI("SELECT * FROM weather.forecast WHERE woeid='" + woeid + "' AND u='" + unit + "'");

        return $pwaRequest.post("/weather/mobile_view/proxy", {
            data: {
                request: btoa("https://query.yahooapis.com/v1/public/yql?q=" + yql + "&format=json&lang=fr-FR")
            },
            cache: false
        });
    };

    return factory;
});
