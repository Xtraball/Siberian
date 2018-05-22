/*global
    App, device, angular
 */

/**
 * SourceCode
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory('SourceCode', function($pwaRequest) {

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
            $pwaRequest.reject("[Factory::SourceCode.find] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if(payload !== false) {

            return $pwaRequest.resolve(payload);

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("sourcecode/mobile_view/find", angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));

        }

    };

    return factory;
});
