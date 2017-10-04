/**
 * Contact
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Contact', function ($pwaRequest) {
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

    factory.find = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Contact.find] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        /** Otherwise fallback on PWA */
        return $pwaRequest.get('contact/mobile_view/find', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.submitForm = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Contact.submitForm] missing value_id');
        }

        return $pwaRequest.post('/contact/mobile_form/post', {
            urlParams: {
                value_id: this.value_id
            },
            data: form,
            cache: false
        });
    };

    return factory;
});
