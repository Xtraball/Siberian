/* global
    App, angular
 */

/**
 * Booking
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Booking', function ($pwaRequest) {
    var factory = {
        value_id: null,
        cache_key: null,
        cache_key_prefix: 'feature_booking_',
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
        factory.cache_key = factory.cache_key_prefix + value_id;
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
            return $pwaRequest.reject('[Factory::Booking.findStores] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

            /** Otherwise fallback on PWA */
            return $pwaRequest.get('booking/mobile_view/find',
                angular.extend({
                    urlParams: {
                        value_id: this.value_id
                    }
                }, factory.extendedOptions)
            );
    };

    factory.submitForm = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Booking.submitForm] missing value_id');
        }

        var data = {};
        for (var prop in form) {
            data[prop] = form[prop];
        }

        data.value_id = this.value_id;

        if (data.date) {
            var date = new Date(data.date);
            var zeroPad = function (e) {
                return ('00' + e).slice(-2);
            };
            // Send date with unknown timezone (timezone will be replaced server side)!
            data.date = date.getFullYear()+ '-' +
                zeroPad(date.getMonth()+1) + '-' +
                zeroPad(date.getDate()) + 'T' +
                zeroPad(date.getHours()) + ':' +
                zeroPad(date.getMinutes()) + ':' +
                zeroPad(date.getSeconds()) + '-00:00';
        }

        return $pwaRequest.post('booking/mobile_view/post', {
            urlParams: {
                value_id: this.value_id
            },
            data: data,
            cache: false
        });
    };

    return factory;
});
