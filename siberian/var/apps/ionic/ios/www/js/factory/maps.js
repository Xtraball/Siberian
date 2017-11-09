/* global
 angular
 */

/**
 * Maps
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Maps', function ($pwaRequest) {
    var factory = {
        value_id: null
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    factory.find = function () {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Maps.find] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        /** Otherwise fallback on PWA */
        return $pwaRequest.get('maps/mobile_view/find', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    return factory;
});
