/**
 * Booking factory
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 */
angular.module("starter").factory("Booking", function ($pwaRequest) {
    var factory = {
        value_id: null,
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.findStores = function () {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Booking.findStores] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        // Otherwise fallback on PWA!
        return $pwaRequest.get("booking/mobile_view/find",
            angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions)
        );
    };

    factory.submitForm = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Booking.submitForm] missing value_id");
        }

        var data = {};
        for (var prop in form) {
            data[prop] = form[prop];
        }

        data.value_id = this.value_id;
        data.version = "2";

        return $pwaRequest.post("booking/mobile_view/post", {
            urlParams: {
                value_id: this.value_id
            },
            data: data,
            cache: false
        });
    };

    return factory;
});
