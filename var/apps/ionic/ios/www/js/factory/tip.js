/*global
 App, device, angular
 */

/**
 * Tip
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Tip", function($pwaRequest) {

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

    factory.findAll = function() {

        if(!this.value_id) {
            $pwaRequest.reject("[Factory::Tip.findAll] missing factory.id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if(payload !== false) {

            return $pwaRequest.resolve(payload);

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("tip/mobile_view/findall", angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));

        }


    };

    return factory;
});
