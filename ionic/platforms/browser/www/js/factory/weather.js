/*global
 App, device, angular, btoa
 */

/**
 * Weather
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Weather", function($q, $pwaRequest) {

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
        if (payload !== false) {
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

    factory.getWeather = function (params) {
        return $pwaRequest.post("weather/mobile_view/getweather", {
            data: params,
            cache: false
        });
    };

    return factory;
});
